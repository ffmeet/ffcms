<?php

namespace App\Filament\Resources\Posts\Schemas;

use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class PostInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->columns(1)
            ->components([
                Section::make('文章内容')
                    ->schema([
                        TextEntry::make('title')
                            ->label('标题'),
                        TextEntry::make('display_author')
                            ->label('署名作者'),
                        TextEntry::make('summary')
                            ->label('摘要')
                            ->columnSpanFull(),
                    ]),
                Section::make('正文')
                    ->schema([
                        TextEntry::make('content')
                            ->label('正文')
                            ->html()
                            ->columnSpanFull(),
                    ]),
            ]);
    }
}
