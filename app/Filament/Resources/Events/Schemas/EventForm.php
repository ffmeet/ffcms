<?php

namespace App\Filament\Resources\Events\Schemas;

use App\Models\MemberGroup;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\KeyValue;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class EventForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('活动信息')
                    ->schema([
                        TextInput::make('title')->label('标题')->required(),
                        TextInput::make('slug')->label('Slug')->required(),
                        Select::make('status')
                            ->label('状态')
                            ->options([
                                'draft' => '草稿',
                                'registration-open' => '报名中',
                                'sold-out' => '已满员',
                                'finished' => '已结束',
                            ])
                            ->default('draft')
                            ->required(),
                        TextInput::make('cover_image_url')->label('封面图'),
                        TextInput::make('location')->label('地点'),
                        Toggle::make('is_paid')->label('付费活动')->default(false),
                        TextInput::make('price')->label('价格')->numeric()->minValue(0)->default(0),
                        TextInput::make('capacity')->label('人数上限')->numeric()->minValue(0),
                        Select::make('required_member_group_id')
                            ->label('限制会员组')
                            ->options(MemberGroup::query()->pluck('name', 'id'))
                            ->searchable(),
                        DateTimePicker::make('starts_at')->label('开始时间'),
                        DateTimePicker::make('ends_at')->label('结束时间'),
                        DateTimePicker::make('registration_opens_at')->label('报名开始'),
                        DateTimePicker::make('registration_closes_at')->label('报名截止'),
                        Textarea::make('summary')->label('摘要')->rows(3)->columnSpanFull(),
                        Textarea::make('content')->label('详情')->rows(8)->columnSpanFull(),
                        KeyValue::make('payload')->label('扩展数据')->columnSpanFull(),
                    ])
                    ->columns(3),
            ]);
    }
}
