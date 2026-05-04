<?php

namespace App\Filament\Resources\EventRegistrations\Tables;

use App\Filament\Resources\Orders\OrderResource;
use App\Filament\Resources\Payments\PaymentResource;
use App\Models\EventRegistration;
use App\Support\CommerceLabels;
use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Notifications\Notification;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Enums\FiltersLayout;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class EventRegistrationsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->heading('活动报名台')
            ->defaultSort('id', 'desc')
            ->columns([
                TextColumn::make('event.title')->label('活动')->searchable()->limit(28),
                TextColumn::make('user.username')->label('会员')->searchable(),
                TextColumn::make('order.order_no')->label('订单号')->searchable()->placeholder('无'),
                TextColumn::make('status')
                    ->label('报名状态')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'approved' => 'success',
                        'cancelled' => 'gray',
                        default => 'warning',
                    })
                    ->formatStateUsing(fn (string $state): string => CommerceLabels::registrationStatus($state)),
                TextColumn::make('payment_status')
                    ->label('支付状态')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'paid' => 'success',
                        'closed' => 'gray',
                        'not_required' => 'info',
                        default => 'warning',
                    })
                    ->formatStateUsing(fn (string $state): string => CommerceLabels::registrationPaymentStatus($state)),
                TextColumn::make('created_at')->label('报名时间')->dateTime('Y-m-d H:i'),
            ])
            ->filters([
                SelectFilter::make('status')
                    ->label('报名状态')
                    ->placeholder('全部状态')
                    ->options([
                        'pending' => '待确认',
                        'approved' => '已确认',
                        'cancelled' => '已取消',
                    ]),
                SelectFilter::make('payment_status')
                    ->label('支付状态')
                    ->placeholder('全部支付状态')
                    ->options([
                        'pending' => '待支付',
                        'paid' => '已支付',
                        'not_required' => '无需支付',
                        'closed' => '已关闭',
                    ]),
            ], layout: FiltersLayout::AboveContent)
            ->deferFilters(false)
            ->filtersFormColumns(2)
            ->emptyStateHeading('还没有活动报名记录')
            ->emptyStateDescription('前台活动报名、支付和人工确认的结果会统一汇总到这里。')
            ->recordActions([
                Action::make('approve')
                    ->label('确认报名')
                    ->icon('heroicon-o-check-circle')
                    ->color('success')
                    ->requiresConfirmation()
                    ->visible(fn (EventRegistration $record): bool => $record->status !== 'approved')
                    ->action(function (EventRegistration $record): void {
                        $record->update([
                            'status' => 'approved',
                            'payment_status' => $record->payment_status === 'closed' ? 'not_required' : $record->payment_status,
                        ]);

                        Notification::make()
                            ->success()
                            ->title('报名已确认')
                            ->body('当前活动报名已经切换为确认状态。')
                            ->send();
                    }),
                Action::make('cancel')
                    ->label('取消报名')
                    ->icon('heroicon-o-no-symbol')
                    ->color('gray')
                    ->requiresConfirmation()
                    ->visible(fn (EventRegistration $record): bool => $record->status !== 'cancelled')
                    ->action(function (EventRegistration $record): void {
                        $record->update([
                            'status' => 'cancelled',
                            'payment_status' => $record->payment_status === 'paid' ? 'paid' : 'closed',
                        ]);

                        Notification::make()
                            ->success()
                            ->title('报名已取消')
                            ->body('当前活动报名已经切换为取消状态。')
                            ->send();
                    }),
                Action::make('order')
                    ->label('查看订单')
                    ->icon('heroicon-o-receipt-percent')
                    ->color('gray')
                    ->visible(fn (EventRegistration $record): bool => filled($record->order_id))
                    ->url(fn (EventRegistration $record): string => OrderResource::getUrl('edit', ['record' => $record->order_id]))
                    ->openUrlInNewTab(),
                Action::make('payment')
                    ->label('查看支付')
                    ->icon('heroicon-o-credit-card')
                    ->color('gray')
                    ->visible(fn (EventRegistration $record): bool => (bool) $record->order?->payments()->exists())
                    ->url(fn (EventRegistration $record): string => PaymentResource::getUrl('edit', ['record' => $record->order->payments()->latest('id')->value('id')]))
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
