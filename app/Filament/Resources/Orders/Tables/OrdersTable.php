<?php

namespace App\Filament\Resources\Orders\Tables;

use App\Filament\Resources\Payments\PaymentResource;
use App\Filament\Resources\UserSubscriptions\UserSubscriptionResource;
use App\Models\Order;
use App\Models\UserSubscription;
use App\Support\CommerceLabels;
use App\Support\PaymentLifecycleManager;
use Filament\Actions\Action;
use Filament\Actions\BulkAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Notifications\Notification;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Enums\FiltersLayout;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Collection;

class OrdersTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->heading('订单台')
            ->defaultSort('id', 'desc')
            ->columns([
                TextColumn::make('order_no')->label('订单号')->searchable(),
                TextColumn::make('user.username')->label('会员')->searchable(),
                TextColumn::make('order_type')
                    ->label('类型')
                    ->badge()
                    ->color('gray')
                    ->formatStateUsing(fn (string $state): string => CommerceLabels::orderType($state)),
                TextColumn::make('title')->label('标题')->limit(28),
                TextColumn::make('amount')->label('金额')->money('CNY'),
                TextColumn::make('payments.0.provider')
                    ->label('支付渠道')
                    ->badge()
                    ->color('gray')
                    ->formatStateUsing(fn (?string $state): string => $state ? CommerceLabels::paymentProvider($state) : '无支付'),
                TextColumn::make('payments.0.status')
                    ->label('支付状态')
                    ->badge()
                    ->color(fn (?string $state): string => match ($state) {
                        'paid' => 'success',
                        'failed' => 'danger',
                        'closed' => 'gray',
                        null => 'gray',
                        default => 'warning',
                    })
                    ->formatStateUsing(fn (?string $state): string => $state ? CommerceLabels::paymentStatus($state) : '无支付'),
                TextColumn::make('status')
                    ->label('状态')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'paid' => 'success',
                        'cancelled', 'closed' => 'gray',
                        'refunded' => 'danger',
                        default => 'warning',
                    })
                    ->formatStateUsing(fn (string $state): string => CommerceLabels::orderStatus($state)),
                TextColumn::make('meta.source')
                    ->label('来源')
                    ->badge()
                    ->color('info')
                    ->formatStateUsing(fn (?string $state): string => match ($state) {
                        'shop-front' => '商品前台',
                        'events-front' => '活动前台',
                        default => $state ?: '系统',
                    }),
                IconColumn::make('is_subscription_linked')
                    ->label('订阅')
                    ->state(fn (Order $record): bool => $record->order_type === 'membership' && UserSubscription::query()->where('last_order_id', $record->id)->exists())
                    ->boolean(),
                TextColumn::make('paid_at')->label('支付时间')->dateTime('Y-m-d H:i'),
                TextColumn::make('updated_at')->label('最后更新')->since(),
            ])
            ->filters([
                SelectFilter::make('order_type')
                    ->label('订单类型')
                    ->placeholder('全部类型')
                    ->options([
                        'membership' => '会员订阅',
                        'product' => '商品',
                        'event' => '活动',
                    ]),
                SelectFilter::make('status')
                    ->label('订单状态')
                    ->placeholder('全部状态')
                    ->options([
                        'pending' => '待支付',
                        'paid' => '已支付',
                        'cancelled' => '已取消',
                        'refunded' => '已退款',
                        'closed' => '已关闭',
                    ]),
                SelectFilter::make('payment_status')
                    ->label('支付状态')
                    ->placeholder('全部支付状态')
                    ->options([
                        'pending' => '待发起',
                        'processing' => '支付中',
                        'paid' => '已支付',
                        'failed' => '失败',
                        'closed' => '已关闭',
                    ])
                    ->query(fn ($query, array $data) => filled($data['value'] ?? null)
                        ? $query->whereHas('payments', fn ($paymentQuery) => $paymentQuery->where('status', $data['value']))
                        : $query),
                TernaryFilter::make('has_payment')
                    ->label('存在支付记录')
                    ->placeholder('全部')
                    ->trueLabel('仅有支付记录')
                    ->falseLabel('仅无支付记录')
                    ->queries(
                        true: fn ($query) => $query->has('payments'),
                        false: fn ($query) => $query->doesntHave('payments'),
                        blank: fn ($query) => $query,
                    ),
            ], layout: FiltersLayout::AboveContent)
            ->deferFilters(false)
            ->filtersFormColumns(2)
            ->emptyStateHeading('还没有订单记录')
            ->emptyStateDescription('前台商品购买、订阅支付和活动报名创建的订单会集中出现在这里。')
            ->recordActions([
                Action::make('payment')
                    ->label('查看支付')
                    ->icon('heroicon-o-credit-card')
                    ->color('gray')
                    ->visible(fn (Order $record): bool => $record->payments()->exists())
                    ->url(fn (Order $record): string => PaymentResource::getUrl('edit', ['record' => $record->payments()->latest('id')->value('id')]))
                    ->openUrlInNewTab(),
                Action::make('subscription')
                    ->label('查看订阅')
                    ->icon('heroicon-o-identification')
                    ->color('gray')
                    ->visible(fn (Order $record): bool => $record->order_type === 'membership' && UserSubscription::query()->where('last_order_id', $record->id)->exists())
                    ->url(fn (Order $record): string => UserSubscriptionResource::getUrl('edit', ['record' => UserSubscription::query()->where('last_order_id', $record->id)->value('id')]))
                    ->openUrlInNewTab(),
                Action::make('markPaid')
                    ->label('标记为已支付')
                    ->icon('heroicon-o-check-circle')
                    ->color('success')
                    ->requiresConfirmation()
                    ->visible(fn (Order $record): bool => $record->payments()->exists() && $record->status !== 'paid')
                    ->action(function (Order $record): void {
                        $payment = $record->payments()->latest('id')->first();

                        if (! $payment) {
                            return;
                        }

                        PaymentLifecycleManager::markPaid($payment, [
                            'source' => 'filament-order-table',
                        ]);
                    }),
                Action::make('closeOrder')
                    ->label('关闭订单')
                    ->icon('heroicon-o-no-symbol')
                    ->color('gray')
                    ->requiresConfirmation()
                    ->visible(fn (Order $record): bool => $record->payments()->exists() && in_array($record->status, ['pending', 'processing'], true))
                    ->action(function (Order $record): void {
                        $payment = $record->payments()->latest('id')->first();

                        if (! $payment) {
                            return;
                        }

                        PaymentLifecycleManager::markClosed($payment, [
                            'source' => 'filament-order-table',
                        ]);
                    }),
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    BulkAction::make('closePendingOrders')
                        ->label('批量关闭待处理订单')
                        ->icon('heroicon-o-no-symbol')
                        ->color('gray')
                        ->requiresConfirmation()
                        ->action(function (Collection $records): void {
                            $handled = 0;

                            foreach ($records as $record) {
                                if (! $record instanceof Order) {
                                    continue;
                                }

                                if (! in_array($record->status, ['pending', 'processing'], true)) {
                                    continue;
                                }

                                $payment = $record->payments()->latest('id')->first();

                                if (! $payment) {
                                    continue;
                                }

                                PaymentLifecycleManager::markClosed($payment, [
                                    'source' => 'filament-order-bulk',
                                ]);

                                $handled++;
                            }

                            Notification::make()
                                ->title('批量关闭完成')
                                ->body('已处理 '.$handled.' 笔待处理订单。')
                                ->success()
                                ->send();
                        }),
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
