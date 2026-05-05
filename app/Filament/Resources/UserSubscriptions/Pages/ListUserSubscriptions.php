<?php

namespace App\Filament\Resources\UserSubscriptions\Pages;

use App\Filament\Pages\PaymentCenter;
use App\Filament\Pages\RouteRuleCenter;
use App\Filament\Resources\Orders\OrderResource;
use App\Filament\Resources\UserSubscriptions\UserSubscriptionResource;
use Filament\Actions\Action;
use Filament\Resources\Pages\ListRecords;

class ListUserSubscriptions extends ListRecords
{
    protected static string $resource = UserSubscriptionResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Action::make('orders')
                ->label('查看订单')
                ->url(OrderResource::getUrl())
                ->color('gray'),
            Action::make('paymentCenter')
                ->label('支付中心')
                ->url(PaymentCenter::getUrl())
                ->color('gray'),
            Action::make('routeRules')
                ->label('规则中心')
                ->url(RouteRuleCenter::getUrl())
                ->color('gray'),
        ];
    }
}
