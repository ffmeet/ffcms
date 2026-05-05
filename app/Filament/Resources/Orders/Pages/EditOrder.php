<?php

namespace App\Filament\Resources\Orders\Pages;

use App\Filament\Resources\Orders\OrderResource;
use App\Filament\Resources\Payments\PaymentResource;
use App\Filament\Resources\UserSubscriptions\UserSubscriptionResource;
use App\Models\Payment;
use App\Models\UserSubscription;
use App\Support\OperationHistory;
use App\Support\PaymentLifecycleManager;
use Filament\Actions\Action;
use Filament\Forms\Components\Textarea;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\EditRecord;

class EditOrder extends EditRecord
{
    protected static string $resource = OrderResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Action::make('viewPayment')
                ->label('查看支付')
                ->icon('heroicon-o-credit-card')
                ->color('gray')
                ->outlined()
                ->visible(fn (): bool => $this->getLatestPaymentRecord() !== null)
                ->url(fn (): string => PaymentResource::getUrl('edit', ['record' => $this->getLatestPaymentRecord()]))
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
                        'meta' => OperationHistory::append($this->record->meta ?? [], 'history', OperationHistory::makeEntry('记录处理备注', 'filament-order-edit', (string) $this->record->status, [
                            'order_no' => $this->record->order_no,
                            'note' => trim((string) $data['note']),
                        ])),
                    ]);

                    $this->record->refresh();

                    Notification::make()
                        ->success()
                        ->title('备注已记录')
                        ->body('这条订单的处理上下文已经追加到最近变化里。')
                        ->send();
                }),
            Action::make('markPaid')
                ->label('标记已支付')
                ->icon('heroicon-o-check-circle')
                ->color('success')
                ->requiresConfirmation()
                ->visible(fn (): bool => $this->record->status !== 'paid' && $this->getLatestPaymentRecord() !== null)
                ->action(function (): void {
                    $payment = $this->getLatestPaymentRecord();

                    if (! $payment) {
                        return;
                    }

                    PaymentLifecycleManager::markPaid($payment, [
                        'source' => 'filament-order-edit',
                    ]);

                    $this->record->refresh();

                    Notification::make()
                        ->success()
                        ->title('订单已同步为已支付')
                        ->body('关联支付、订阅或业务记录已经按支付成功流程回写。')
                        ->send();
                }),
            Action::make('closeOrder')
                ->label('关闭订单')
                ->icon('heroicon-o-no-symbol')
                ->color('gray')
                ->requiresConfirmation()
                ->visible(fn (): bool => ! in_array($this->record->status, ['paid', 'closed', 'cancelled', 'refunded'], true))
                ->action(function (): void {
                    $payment = $this->getLatestPaymentRecord();

                    if ($payment) {
                        PaymentLifecycleManager::markClosed($payment, [
                            'source' => 'filament-order-edit',
                        ]);
                    } else {
                        $this->record->update([
                            'status' => 'closed',
                            'meta' => OperationHistory::append($this->record->meta ?? [], 'history', OperationHistory::makeEntry('订单已关闭', 'filament-order-edit', 'closed', [
                                'order_no' => $this->record->order_no,
                            ])),
                        ]);

                        if ($this->record->order_type === 'membership') {
                            UserSubscription::query()
                                ->where('last_order_id', $this->record->id)
                                ->where('status', 'pending')
                                ->get()
                                ->each(function (UserSubscription $subscription): void {
                                    $subscription->update([
                                        'status' => 'cancelled',
                                        'meta' => OperationHistory::append($subscription->meta ?? [], 'history', OperationHistory::makeEntry('订阅已取消续费', 'filament-order-edit', 'cancelled', [
                                            'order_no' => $this->record->order_no,
                                        ])),
                                    ]);
                                });
                        }
                    }

                    $this->record->refresh();

                    Notification::make()
                        ->success()
                        ->title('订单已关闭')
                        ->body('相关待处理链路已经同步调整。')
                        ->send();
                }),
        ];
    }

    protected function getLatestPaymentRecord(): ?Payment
    {
        return $this->record->payments()->latest('id')->first();
    }

    protected function getLinkedSubscriptionRecord(): ?UserSubscription
    {
        if ($this->record->order_type !== 'membership') {
            return null;
        }

        return UserSubscription::query()
            ->where('last_order_id', $this->record->id)
            ->latest('id')
            ->first();
    }
}
