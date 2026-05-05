<?php

namespace App\Filament\Resources\Payments\Pages;

use App\Filament\Pages\PaymentCenter;
use App\Filament\Resources\Orders\OrderResource;
use App\Filament\Resources\Payments\PaymentResource;
use Filament\Actions\Action;
use Filament\Resources\Pages\ListRecords;

class ListPayments extends ListRecords
{
    protected static string $resource = PaymentResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Action::make('paymentCenter')
                ->label('支付中心')
                ->url(PaymentCenter::getUrl())
                ->color('gray'),
            Action::make('orders')
                ->label('查看订单')
                ->url(OrderResource::getUrl())
                ->color('gray'),
        ];
    }
}
