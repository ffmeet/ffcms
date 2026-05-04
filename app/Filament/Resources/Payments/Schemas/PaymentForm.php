<?php

namespace App\Filament\Resources\Payments\Schemas;

use App\Models\Order;
use App\Support\BackofficeOperationInsights;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\KeyValue;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Group;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Illuminate\Support\HtmlString;

class PaymentForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Grid::make([
                    'default' => 1,
                    'xl' => 12,
                ])->schema([
                    Section::make('支付链路')
                        ->compact()
                        ->schema([
                            Placeholder::make('payment_operations_overview')
                                ->hiddenLabel()
                                ->content(fn ($record): HtmlString|string => BackofficeOperationInsights::paymentOverview($record)),
                        ])
                        ->columnSpan([
                            'xl' => 5,
                        ]),
                    Group::make([
                        Section::make('支付信息')
                            ->schema([
                                Select::make('order_id')
                                    ->label('关联订单')
                                    ->options(Order::query()->orderByDesc('id')->pluck('order_no', 'id'))
                                    ->searchable()
                                    ->required(),
                                Select::make('provider')
                                    ->label('支付渠道')
                                    ->options([
                                        'wechat' => '微信支付',
                                        'alipay' => '支付宝',
                                        'paypal' => 'PayPal',
                                        'stripe' => 'Stripe',
                                        'manual' => '线下/人工',
                                    ])
                                    ->required(),
                                TextInput::make('provider_payment_no')->label('渠道流水号'),
                                Select::make('status')
                                    ->label('状态')
                                    ->options([
                                        'pending' => '待发起',
                                        'processing' => '支付中',
                                        'paid' => '已支付',
                                        'failed' => '失败',
                                        'closed' => '已关闭',
                                    ])
                                    ->required(),
                                TextInput::make('amount')->label('金额')->numeric()->required()->minValue(0),
                                DateTimePicker::make('paid_at')->label('支付时间'),
                                KeyValue::make('payload')->label('回调/扩展数据')->columnSpanFull(),
                            ])
                            ->columns(3),
                    ])
                        ->columnSpan([
                            'xl' => 7,
                        ]),
                ]),
            ]);
    }
}
