<?php

namespace App\Filament\Resources\EventRegistrations\Schemas;

use App\Models\Event;
use App\Models\Order;
use App\Models\User;
use Filament\Forms\Components\KeyValue;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class EventRegistrationForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('报名信息')
                    ->schema([
                        Select::make('event_id')
                            ->label('活动')
                            ->options(Event::query()->orderByDesc('id')->pluck('title', 'id'))
                            ->searchable()
                            ->required(),
                        Select::make('user_id')
                            ->label('会员')
                            ->options(User::query()->orderBy('username')->pluck('username', 'id'))
                            ->searchable()
                            ->required(),
                        Select::make('order_id')
                            ->label('关联订单')
                            ->options(Order::query()->orderByDesc('id')->pluck('order_no', 'id'))
                            ->searchable(),
                        Select::make('status')
                            ->label('报名状态')
                            ->options([
                                'pending' => '待确认',
                                'approved' => '已确认',
                                'cancelled' => '已取消',
                            ])
                            ->required(),
                        Select::make('payment_status')
                            ->label('支付状态')
                            ->options([
                                'pending' => '待支付',
                                'paid' => '已支付',
                                'not_required' => '无需支付',
                                'closed' => '已关闭',
                            ])
                            ->required(),
                        Textarea::make('notes')
                            ->label('备注')
                            ->rows(4)
                            ->columnSpanFull(),
                        KeyValue::make('payload')
                            ->label('扩展数据')
                            ->columnSpanFull(),
                    ])
                    ->columns(3),
            ]);
    }
}
