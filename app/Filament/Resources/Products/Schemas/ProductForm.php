<?php

namespace App\Filament\Resources\Products\Schemas;

use Filament\Forms\Components\KeyValue;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class ProductForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('商品信息')
                    ->schema([
                        TextInput::make('title')->label('标题')->required(),
                        TextInput::make('slug')->label('Slug')->required(),
                        Select::make('status')
                            ->label('状态')
                            ->options([
                                'draft' => '草稿',
                                'published' => '上架',
                                'archived' => '下架',
                            ])
                            ->default('draft')
                            ->required(),
                        TextInput::make('cover_image_url')->label('封面图'),
                        TextInput::make('price')->label('售价')->numeric()->required()->minValue(0),
                        TextInput::make('compare_at_price')->label('原价')->numeric()->minValue(0),
                        TextInput::make('stock')->label('库存')->numeric()->minValue(0),
                        Select::make('delivery_type')
                            ->label('交付类型')
                            ->options([
                                'download' => '下载',
                                'membership' => '会员权益',
                                'event-access' => '活动资格',
                                'physical' => '实体商品',
                            ])
                            ->default('download')
                            ->required(),
                        Textarea::make('summary')->label('摘要')->rows(3)->columnSpanFull(),
                        Textarea::make('content')->label('详情')->rows(8)->columnSpanFull(),
                        KeyValue::make('payload')->label('扩展数据')->columnSpanFull(),
                    ])
                    ->columns(3),
            ]);
    }
}
