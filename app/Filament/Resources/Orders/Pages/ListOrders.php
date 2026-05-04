<?php

namespace App\Filament\Resources\Orders\Pages;

use App\Filament\Pages\PaymentCenter;
use App\Filament\Resources\Orders\OrderResource;
use App\Filament\Resources\Payments\PaymentResource;
use App\Filament\Resources\UserSubscriptions\UserSubscriptionResource;
use Filament\Actions\Action;
use Filament\Resources\Pages\ListRecords;

class ListOrders extends ListRecords
{
    protected static string $resource = OrderResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Action::make('payments')
                ->label('支付记录')
                ->url(PaymentResource::getUrl())
                ->color('gray'),
            Action::make('subscriptions')
                ->label('用户订阅')
                ->url(UserSubscriptionResource::getUrl())
                ->color('gray'),
            Action::make('paymentCenter')
                ->label('支付中心')
                ->url(PaymentCenter::getUrl())
                ->color('gray'),
        ];
    }
}
