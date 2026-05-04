<?php

namespace App\Filament\Resources\Payments\Pages;

use App\Filament\Resources\Orders\OrderResource;
use App\Filament\Resources\Payments\PaymentResource;
use App\Filament\Resources\UserSubscriptions\UserSubscriptionResource;
use App\Models\UserSubscription;
use App\Support\OperationHistory;
use App\Support\PaymentLifecycleManager;
use Filament\Actions\Action;
use Filament\Forms\Components\Textarea;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\EditRecord;

class EditPayment extends EditRecord
{
    protected static string $resource = PaymentResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Action::make('viewOrder')
                ->label('查看订单')
                ->icon('heroicon-o-receipt-percent')
                ->color('gray')
                ->outlined()
                ->visible(fn (): bool => $this->record->order !== null)
                ->url(fn (): string => OrderResource::getUrl('edit', ['record' => $this->record->order]))
                ->openUrlInNewTab(),
            Action::make('viewSubscription')
                ->label('查看订阅')
                ->icon('heroicon-o-identification')
                ->color('gray')
                ->outlined()
                ->visible(fn (): bool => $this->getLinkedSubscriptionRecord() !== null)
                ->url(fn (): string => UserSubscriptionResource::getUrl('edit', ['record' => $this->getLinkedSubscriptionRecord()]))
                ->openUrlInNewTab(),
            Action::make('addNote')
                ->label('记录处理备注')
                ->icon('heroicon-o-pencil-square')
                ->color('gray')
                ->outlined()
                ->form([
                    Textarea::make('note')
                        ->label('处理备注')
                        ->rows(4)
                        ->required()
                        ->maxLength(500),
                ])
                ->action(function (array $data): void {
                    $this->record->update([
                        'payload' => OperationHistory::append($this->record->payload ?? [], 'history', OperationHistory::makeEntry('记录处理备注', 'filament-payment-edit', (string) $this->record->status, [
                            'provider' => $this->record->provider,
                            'order_no' => $this->record->order?->order_no,
                            'entry' => data_get($this->record->payload ?? [], 'entry'),
                            'note' => trim((string) $data['note']),
                        ])),
                    ]);

                    $this->record->refresh();

                    Notification::make()
                        ->success()
                        ->title('备注已记录')
                        ->body('这条支付的处理上下文已经追加到最近变化里。')
                        ->send();
                }),
            Action::make('markPaid')
                ->label('标记支付成功')
                ->icon('heroicon-o-check-circle')
                ->color('success')
                ->requiresConfirmation()
                ->visible(fn (): bool => $this->record->status !== 'paid')
                ->action(function (): void {
                    PaymentLifecycleManager::markPaid($this->record, [
                        'source' => 'filament-edit',
                    ]);

                    $this->record->refresh();

                    Notification::make()
                        ->success()
                        ->title('支付已标记成功')
                        ->body('订单、订阅或活动报名状态已经同步回写。')
                        ->send();
                }),
            Action::make('markFailed')
                ->label('标记支付失败')
                ->icon('heroicon-o-x-circle')
                ->color('danger')
                ->requiresConfirmation()
                ->visible(fn (): bool => in_array($this->record->status, ['pending', 'processing'], true))
                ->action(function (): void {
                    PaymentLifecycleManager::markFailed($this->record, [
                        'source' => 'filament-edit',
                    ]);

                    $this->record->refresh();

                    Notification::make()
                        ->warning()
                        ->title('支付已标记失败')
                        ->body('支付记录已更新，订单保留待处理状态。')
                        ->send();
                }),
            Action::make('markClosed')
                ->label('关闭支付')
                ->icon('heroicon-o-no-symbol')
                ->color('gray')
                ->requiresConfirmation()
                ->visible(fn (): bool => in_array($this->record->status, ['pending', 'processing', 'failed'], true))
                ->action(function (): void {
                    PaymentLifecycleManager::markClosed($this->record, [
                        'source' => 'filament-edit',
                    ]);

                    $this->record->refresh();

                    Notification::make()
                        ->success()
                        ->title('支付已关闭')
                        ->body('关联订单和待处理业务记录已经同步关闭。')
                        ->send();
                }),
        ];
    }

    protected function getLinkedSubscriptionRecord(): ?UserSubscription
    {
        $order = $this->record->order;

        if (! $order || $order->order_type !== 'membership') {
            return null;
        }

        return UserSubscription::query()
            ->where('last_order_id', $order->id)
            ->latest('id')
            ->first();
    }
}
