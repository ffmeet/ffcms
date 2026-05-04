<?php

namespace App\Filament\Resources\UserSubscriptions\Tables;

use App\Filament\Resources\Orders\OrderResource;
use App\Filament\Resources\Payments\PaymentResource;
use App\Models\UserSubscription;
use App\Support\CommerceLabels;
use Filament\Actions\Action;
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

class UserSubscriptionsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->heading('订阅台')
            ->defaultSort('id', 'desc')
            ->columns([
                TextColumn::make('user.username')->label('会员')->searchable(),
                TextColumn::make('plan.name')->label('套餐')->searchable(),
                TextColumn::make('lastOrder.order_no')
                    ->label('最近订单')
                    ->searchable()
                    ->placeholder('暂无'),
                TextColumn::make('status')
                    ->label('状态')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'active' => 'success',
                        'cancelled' => 'gray',
                        'inactive' => 'danger',
                        default => 'warning',
                    })
                    ->formatStateUsing(fn (string $state): string => CommerceLabels::subscriptionStatus($state)),
                TextColumn::make('lastOrder.status')
                    ->label('订单状态')
                    ->badge()
                    ->color(fn (?string $state): string => match ($state) {
                        'paid' => 'success',
                        'closed', 'cancelled' => 'gray',
                        'refunded' => 'danger',
                        null => 'gray',
                        default => 'warning',
                    })
                    ->formatStateUsing(fn (?string $state): string => $state ? CommerceLabels::orderStatus($state) : '无订单'),
                TextColumn::make('lastOrder.payments.0.status')
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
                IconColumn::make('auto_renew')->label('自动续费')->boolean(),
                TextColumn::make('started_at')->label('生效时间')->dateTime('Y-m-d H:i'),
                TextColumn::make('expires_at')->label('到期时间')->dateTime('Y-m-d H:i'),
            ])
            ->filters([
                SelectFilter::make('status')
                    ->label('订阅状态')
                    ->placeholder('全部状态')
                    ->options([
                        'active' => '生效中',
                        'expired' => '已过期',
                        'cancelled' => '已取消续费',
                        'pending' => '待生效',
                    ]),
                SelectFilter::make('auto_renew')
                    ->label('自动续费')
                    ->placeholder('全部')
                    ->options([
                        '1' => '自动续费中',
                        '0' => '未开启',
                    ])
                    ->query(function ($query, array $data) {
                        if (! filled($data['value'] ?? null)) {
                            return $query;
                        }

                        return $query->where('auto_renew', $data['value'] === '1');
                    }),
                SelectFilter::make('plan_id')
                    ->label('套餐')
                    ->relationship('plan', 'name')
                    ->searchable()
                    ->preload(),
                TernaryFilter::make('has_unpaid_order')
                    ->label('待处理订单')
                    ->placeholder('全部')
                    ->trueLabel('仅待处理')
                    ->falseLabel('仅已支付/无订单')
                    ->queries(
                        true: fn ($query) => $query->whereHas('lastOrder', fn ($orderQuery) => $orderQuery->whereIn('status', ['pending', 'processing'])),
                        false: fn ($query) => $query->where(function ($inner) {
                            $inner->whereDoesntHave('lastOrder')
                                ->orWhereHas('lastOrder', fn ($orderQuery) => $orderQuery->whereNotIn('status', ['pending', 'processing']));
                        }),
                        blank: fn ($query) => $query,
                    ),
            ], layout: FiltersLayout::AboveContent)
            ->deferFilters(false)
            ->filtersFormColumns(2)
            ->emptyStateHeading('还没有订阅记录')
            ->emptyStateDescription('前台套餐购买成功后，订阅记录会自动沉淀到这里。')
            ->recordActions([
                Action::make('activate')
                    ->label('手动生效')
                    ->icon('heroicon-o-check-badge')
                    ->color('success')
                    ->requiresConfirmation()
                    ->visible(fn (UserSubscription $record): bool => $record->status !== 'active')
                    ->action(function (UserSubscription $record): void {
                        $durationDays = (int) ($record->plan?->duration_days ?? 30);

                        $record->update([
                            'status' => 'active',
                            'started_at' => $record->started_at ?? now(),
                            'expires_at' => now()->addDays($durationDays),
                        ]);

                        Notification::make()
                            ->success()
                            ->title('订阅已生效')
                            ->body('当前订阅已经切换为生效状态。')
                            ->send();
                    }),
                Action::make('cancel')
                    ->label('取消订阅')
                    ->icon('heroicon-o-no-symbol')
                    ->color('gray')
                    ->requiresConfirmation()
                    ->visible(fn (UserSubscription $record): bool => $record->status !== 'cancelled')
                    ->action(function (UserSubscription $record): void {
                        $record->update([
                            'status' => 'cancelled',
                        ]);

                        Notification::make()
                            ->success()
                            ->title('订阅已取消')
                            ->body('当前订阅已经切换为取消状态。')
                            ->send();
                    }),
                Action::make('openPendingPayment')
                    ->label('处理待支付')
                    ->icon('heroicon-o-bolt')
                    ->color('warning')
                    ->visible(fn (UserSubscription $record): bool => (bool) $record->lastOrder && in_array($record->lastOrder->status, ['pending', 'processing'], true))
                    ->url(fn (UserSubscription $record): string => OrderResource::getUrl('edit', ['record' => $record->last_order_id]))
                    ->openUrlInNewTab(),
                Action::make('order')
                    ->label('查看订单')
                    ->icon('heroicon-o-receipt-percent')
                    ->color('gray')
                    ->visible(fn (UserSubscription $record): bool => filled($record->last_order_id))
                    ->url(fn (UserSubscription $record): string => OrderResource::getUrl('edit', ['record' => $record->last_order_id]))
                    ->openUrlInNewTab(),
                Action::make('payment')
                    ->label('查看支付')
                    ->icon('heroicon-o-credit-card')
                    ->color('gray')
                    ->visible(fn (UserSubscription $record): bool => (bool) $record->lastOrder?->payments()->exists())
                    ->url(fn (UserSubscription $record): string => PaymentResource::getUrl('edit', ['record' => $record->lastOrder->payments()->latest('id')->value('id')]))
                    ->openUrlInNewTab(),
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
