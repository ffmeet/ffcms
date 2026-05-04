<?php

namespace App\Filament\Resources\UserSubscriptions\Schemas;

use App\Models\MembershipPlan;
use App\Models\Order;
use App\Models\User;
use App\Support\BackofficeOperationInsights;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\KeyValue;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Group;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Illuminate\Support\HtmlString;

class UserSubscriptionForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Grid::make([
                    'default' => 1,
                    'xl' => 12,
                ])->schema([
                    Section::make('订阅链路')
                        ->compact()
                        ->schema([
                            Placeholder::make('subscription_operations_overview')
                                ->hiddenLabel()
                                ->content(fn ($record): HtmlString|string => BackofficeOperationInsights::subscriptionOverview($record)),
                        ])
                        ->columnSpan([
                            'xl' => 5,
                        ]),
                    Group::make([
                        Section::make('订阅信息')
                            ->schema([
                                Select::make('user_id')
                                    ->label('会员')
                                    ->options(User::query()->orderBy('username')->pluck('username', 'id'))
                                    ->searchable()
                                    ->required(),
                                Select::make('plan_id')
                                    ->label('套餐')
                                    ->options(MembershipPlan::query()->orderBy('sort_order')->pluck('name', 'id'))
                                    ->searchable()
                                    ->required(),
                                Select::make('last_order_id')
                                    ->label('最近订单')
                                    ->options(Order::query()->orderByDesc('id')->pluck('order_no', 'id'))
                                    ->searchable(),
                                Select::make('status')
                                    ->label('状态')
                                    ->options([
                                        'active' => '生效中',
                                        'expired' => '已过期',
                                        'cancelled' => '已取消续费',
                                        'pending' => '待生效',
                                    ])
                                    ->required(),
                                Toggle::make('auto_renew')->label('自动续费'),
                                DateTimePicker::make('started_at')->label('生效时间'),
                                DateTimePicker::make('expires_at')->label('到期时间'),
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
