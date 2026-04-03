<?php

namespace App\Filament\Resources\Comments\Schemas;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Schemas\Schema;

class CommentForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('user_id')
                    ->relationship('user', 'username')
                    ->label('评论用户')
                    ->searchable()
                    ->preload(),
                Select::make('post_id')
                    ->relationship('post', 'title')
                    ->label('所属文章')
                    ->required(),
                Select::make('parent_id')
                    ->relationship('parent', 'id')
                    ->label('父评论')
                    ->searchable()
                    ->preload(),
                Textarea::make('content')
                    ->required()
                    ->columnSpanFull()
                    ->rows(5),
                Select::make('status')
                    ->required()
                    ->options([
                        'pending' => '待审核',
                        'approved' => '已通过',
                        'rejected' => '已驳回',
                    ])
                    ->default('pending')
                    ->native(false),
            ]);
    }
}
