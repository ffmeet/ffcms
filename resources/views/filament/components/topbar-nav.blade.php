@php
    $items = [
        ['label' => '商品', 'pattern' => 'filament.admin.resources.products.*', 'url' => \App\Filament\Resources\Products\ProductResource::getUrl()],
        ['label' => '订单', 'pattern' => 'filament.admin.resources.orders.*', 'url' => \App\Filament\Resources\Orders\OrderResource::getUrl()],
        ['label' => '支付', 'pattern' => 'filament.admin.resources.payments.*', 'url' => \App\Filament\Resources\Payments\PaymentResource::getUrl()],
        ['label' => '订阅', 'pattern' => 'filament.admin.resources.user-subscriptions.*', 'url' => \App\Filament\Resources\UserSubscriptions\UserSubscriptionResource::getUrl()],
        ['label' => '报名', 'pattern' => 'filament.admin.resources.event-registrations.*', 'url' => \App\Filament\Resources\EventRegistrations\EventRegistrationResource::getUrl()],
    ];
@endphp

<div class="ecms-topbar-nav hidden xl:flex xl:items-center xl:gap-6">
    @foreach ($items as $item)
        <a
            href="{{ $item['url'] }}"
            @class([
                'ecms-topbar-link',
                'is-active' => request()->routeIs($item['pattern']),
            ])
        >
            {{ $item['label'] }}
        </a>
    @endforeach
</div>
