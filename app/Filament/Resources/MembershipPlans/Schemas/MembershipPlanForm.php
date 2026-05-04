<?php

namespace App\Filament\Resources\MembershipPlans\Schemas;

use Filament\Forms\Components\KeyValue;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class MembershipPlanForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('套餐信息')
                    ->schema([
                        TextInput::make('name')->label('名称')->required(),
                        TextInput::make('slug')->label('Slug')->required(),
                        TextInput::make('price')->label('价格')->numeric()->required()->minValue(0),
                        Select::make('billing_period')
                            ->label('计费周期')
                            ->options([
                                'monthly' => '月付',
                                'yearly' => '年付',
                                'once' => '一次性',
                            ])
                            ->default('monthly')
                            ->required(),
                        TextInput::make('duration_days')->label('有效天数')->numeric()->required()->minValue(1),
                        Toggle::make('is_active')->label('启用')->default(true),
                        Textarea::make('description')->label('描述')->rows(4)->columnSpanFull(),
                        KeyValue::make('features')->label('权益说明')->columnSpanFull(),
                    ])
                    ->columns(3),
            ]);
    }
}
