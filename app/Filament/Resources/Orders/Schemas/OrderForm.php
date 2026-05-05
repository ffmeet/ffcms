<?php

namespace App\Filament\Resources\Orders\Schemas;

use App\Models\User;
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

class OrderForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Grid::make([
                    'default' => 1,
                    'xl' => 12,
                ])->schema([
                    Section::make('订单链路')
                        ->compact()
                        ->schema([
                            Placeholder::make('order_operations_overview')
                                ->hiddenLabel()
                                ->content(fn ($record): HtmlString|string => BackofficeOperationInsights::orderOverview($record)),
                        ])
                        ->columnSpan([
                            'xl' => 5,
                        ]),
                    Group::make([
                        Section::make('订单信息')
                            ->schema([
                                Select::make('user_id')
                                    ->label('会员')
                                    ->options(User::query()->orderBy('username')->pluck('username', 'id'))
                                    ->searchable()
                                    ->required(),
                                TextInput::make('order_no')->label('订单号')->required(),
                                Select::make('order_type')
                                    ->label('订单类型')
                                    ->options([
                                        'membership' => '会员订阅',
                                        'product' => '商品',
                                        'event' => '活动',
                                    ])
                                    ->required(),
                                TextInput::make('title')->label('订单标题')->required()->columnSpanFull(),
                                TextInput::make('currency')->label('货币')->default('CNY')->required(),
                                TextInput::make('amount')->label('金额')->numeric()->required()->minValue(0),
                                Select::make('status')
                                    ->label('状态')
                                    ->options([
                                        'pending' => '待支付',
                                        'paid' => '已支付',
                                        'cancelled' => '已取消',
                                        'refunded' => '已退款',
                                        'closed' => '已关闭',
                                    ])
                                    ->required(),
                                DateTimePicker::make('paid_at')->label('支付时间'),
                                KeyValue::make('meta')->label('扩展数据')->columnSpanFull(),
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
