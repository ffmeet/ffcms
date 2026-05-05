<?php

namespace App\Filament\Resources\Payments\Tables;

use App\Filament\Resources\Orders\OrderResource;
use App\Filament\Resources\UserSubscriptions\UserSubscriptionResource;
use App\Models\Payment;
use App\Models\UserSubscription;
use App\Support\CommerceLabels;
use App\Support\PaymentLifecycleManager;
use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Notifications\Notification;
use Filament\Tables\Enums\FiltersLayout;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class PaymentsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->heading('支付台')
            ->defaultSort('id', 'desc')
            ->columns([
                TextColumn::make('order.order_no')
                    ->label('订单号')
                    ->searchable(),
                TextColumn::make('order.user.username')
                    ->label('会员')
                    ->searchable(),
                TextColumn::make('order.order_type')
                    ->label('订单类型')
                    ->badge()
                    ->color('gray')
                    ->formatStateUsing(fn (?string $state): string => $state ? CommerceLabels::orderType($state) : '未关联订单'),
                TextColumn::make('order.title')
                    ->label('订单标题')
                    ->limit(24)
                    ->searchable(),
                TextColumn::make('provider')
                    ->label('渠道')
                    ->badge()
                    ->color('gray')
                    ->formatStateUsing(fn (string $state): string => CommerceLabels::paymentProvider($state)),
                TextColumn::make('provider_payment_no')
                    ->label('流水号')
                    ->limit(24)
                    ->searchable(),
                TextColumn::make('amount')->label('金额')->money('CNY'),
                TextColumn::make('status')
                    ->label('状态')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'paid' => 'success',
                        'processing' => 'warning',
                        'failed' => 'danger',
                        'closed' => 'gray',
                        default => 'info',
                    }),
                TextColumn::make('payload.entry')
                    ->label('来源')
                    ->badge()
                    ->color('info')
                    ->formatStateUsing(fn (?string $state): string => match ($state) {
                        'shop' => '商品下单',
                        'events' => '活动报名',
                        'member-checkout' => '会员支付页',
                        default => $state ?: '系统',
                    }),
                TextColumn::make('paid_at')->label('支付时间')->dateTime('Y-m-d H:i'),
                TextColumn::make('updated_at')->label('最后更新')->since(),
            ])
            ->filters([
                SelectFilter::make('provider')
                    ->label('支付渠道')
                    ->placeholder('全部渠道')
                    ->options([
                        'wechat' => '微信支付',
                        'alipay' => '支付宝',
                        'paypal' => 'PayPal',
                        'stripe' => 'Stripe',
                        'manual' => '线下 / 人工',
                    ]),
                SelectFilter::make('status')
                    ->label('支付状态')
                    ->placeholder('全部状态')
                    ->options([
                        'pending' => '待发起',
                        'processing' => '支付中',
                        'paid' => '已支付',
                        'failed' => '失败',
                        'closed' => '已关闭',
                    ]),
                SelectFilter::make('order_type')
                    ->label('订单类型')
                    ->options([
                        'membership' => '会员订阅',
                        'product' => '商品',
                        'event' => '活动',
                    ])
                    ->query(fn ($query, array $data) => filled($data['value'] ?? null)
                        ? $query->whereHas('order', fn ($orderQuery) => $orderQuery->where('order_type', $data['value']))
                        : $query),
                TernaryFilter::make('simulated')
                    ->label('模拟支付')
                    ->placeholder('全部')
                    ->trueLabel('仅模拟')
                    ->falseLabel('仅真实 / 未标记')
                    ->queries(
                        true: fn ($query) => $query->where('payload->simulated', true),
                        false: fn ($query) => $query->where(function ($inner) {
                            $inner->whereNull('payload->simulated')
                                ->orWhere('payload->simulated', false);
                        }),
                        blank: fn ($query) => $query,
                    ),
            ], layout: FiltersLayout::AboveContent)
            ->deferFilters(false)
            ->filtersFormColumns(2)
            ->emptyStateHeading('还没有支付记录')
            ->emptyStateDescription('前台下单、会员支付和活动报名产生的支付记录会集中沉淀到这里。')
            ->recordActions([
                Action::make('order')
                    ->label('查看订单')
                    ->icon('heroicon-o-receipt-percent')
                    ->color('gray')
                    ->visible(fn (Payment $record): bool => filled($record->order_id))
                    ->url(fn (Payment $record): string => OrderResource::getUrl('edit', ['record' => $record->order_id]))
                    ->openUrlInNewTab(),
                Action::make('subscription')
                    ->label('查看订阅')
                    ->icon('heroicon-o-identification')
                    ->color('gray')
                    ->visible(fn (Payment $record): bool => $record->order?->order_type === 'membership' && UserSubscription::query()->where('last_order_id', $record->order_id)->exists())
                    ->url(fn (Payment $record): string => UserSubscriptionResource::getUrl('edit', ['record' => UserSubscription::query()->where('last_order_id', $record->order_id)->value('id')]))
                    ->openUrlInNewTab(),
                Action::make('markPaid')
                    ->label('标记支付成功')
                    ->icon('heroicon-o-check-circle')
                    ->color('success')
                    ->requiresConfirmation()
                    ->visible(fn (Payment $record): bool => $record->status !== 'paid')
                    ->action(function (Payment $record): void {
                        PaymentLifecycleManager::markPaid($record, [
                            'source' => 'filament-manual',
                        ]);
                    }),
                Action::make('markFailed')
                    ->label('标记支付失败')
                    ->icon('heroicon-o-x-circle')
                    ->color('danger')
                    ->requiresConfirmation()
                    ->visible(fn (Payment $record): bool => in_array($record->status, ['pending', 'processing'], true))
                    ->action(function (Payment $record): void {
                        PaymentLifecycleManager::markFailed($record, [
                            'source' => 'filament-manual',
                        ]);
                    }),
                Action::make('markClosed')
                    ->label('关闭支付')
                    ->icon('heroicon-o-no-symbol')
                    ->color('gray')
                    ->requiresConfirmation()
                    ->visible(fn (Payment $record): bool => in_array($record->status, ['pending', 'processing', 'failed'], true))
                    ->action(function (Payment $record): void {
                        PaymentLifecycleManager::markClosed($record, [
                            'source' => 'filament-manual',
                        ]);
                    }),
                Action::make('copyWebhook')
                    ->label('复制 webhook 线索')
                    ->icon('heroicon-o-link')
                    ->color('info')
                    ->visible(fn (Payment $record): bool => filled($record->provider))
                    ->action(function (Payment $record): void {
                        Notification::make()
                            ->title('支付线索')
                            ->body('渠道：'.CommerceLabels::paymentProvider($record->provider).' / 来源：'.(data_get($record->payload, 'entry') ?: '系统').' / 订单：'.($record->order?->order_no ?: '无'))
                            ->success()
                            ->send();
                    }),
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
