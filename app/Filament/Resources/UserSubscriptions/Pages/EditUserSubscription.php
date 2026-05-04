<?php

namespace App\Filament\Resources\UserSubscriptions\Pages;

use App\Filament\Resources\Orders\OrderResource;
use App\Filament\Resources\Payments\PaymentResource;
use App\Filament\Resources\UserSubscriptions\UserSubscriptionResource;
use App\Models\Payment;
use App\Support\OperationHistory;
use Filament\Actions\Action;
use Filament\Forms\Components\Textarea;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\EditRecord;

class EditUserSubscription extends EditRecord
{
    protected static string $resource = UserSubscriptionResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Action::make('viewOrder')
                ->label('查看订单')
                ->icon('heroicon-o-receipt-percent')
                ->color('gray')
                ->outlined()
                ->visible(fn (): bool => $this->record->lastOrder !== null)
                ->url(fn (): string => OrderResource::getUrl('edit', ['record' => $this->record->lastOrder]))
                ->openUrlInNewTab(),
            Action::make('viewPayment')
                ->label('查看支付')
                ->icon('heroicon-o-credit-card')
                ->color('gray')
                ->outlined()
                ->visible(fn (): bool => $this->getLatestPaymentRecord() !== null)
                ->url(fn (): string => PaymentResource::getUrl('edit', ['record' => $this->getLatestPaymentRecord()]))
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
                        'meta' => OperationHistory::append($this->record->meta ?? [], 'history', OperationHistory::makeEntry('记录处理备注', 'filament-subscription-edit', (string) $this->record->status, [
                            'order_no' => $this->record->lastOrder?->order_no,
                            'note' => trim((string) $data['note']),
                        ])),
                    ]);

                    $this->record->refresh();

                    Notification::make()
                        ->success()
                        ->title('备注已记录')
                        ->body('这条订阅的处理上下文已经追加到最近变化里。')
                        ->send();
                }),
            Action::make('activateSubscription')
                ->label('立即生效')
                ->icon('heroicon-o-check-badge')
                ->color('success')
                ->requiresConfirmation()
                ->visible(fn (): bool => $this->record->status === 'pending' && $this->record->lastOrder?->status === 'paid')
                ->action(function (): void {
                    $durationDays = (int) ($this->record->plan?->duration_days ?? 30);

                    $this->record->update([
                        'status' => 'active',
                        'started_at' => $this->record->started_at ?? now(),
                        'expires_at' => $this->record->expires_at ?? now()->addDays($durationDays),
                        'meta' => OperationHistory::append($this->record->meta ?? [], 'history', OperationHistory::makeEntry('订阅手动生效', 'filament-subscription-edit', 'active', [
                            'order_no' => $this->record->lastOrder?->order_no,
                        ])),
                    ]);

                    $this->record->refresh();

                    Notification::make()
                        ->success()
                        ->title('订阅已生效')
                        ->body('已经按当前套餐周期补齐生效时间和到期时间。')
                        ->send();
                }),
        ];
    }

    protected function getLatestPaymentRecord(): ?Payment
    {
        return $this->record->lastOrder?->payments()->latest('id')->first();
    }
}
