<?php

namespace App\Filament\Resources\Posts\RelationManagers;

use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\TextInput;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class StatisticsRelationManager extends RelationManager
{
    protected static string $relationship = 'statistics';

    protected static ?string $title = '统计';

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('views')
                    ->label('浏览量')
                    ->required()
                    ->numeric()
                    ->default(0)
                    ->minValue(0),
                TextInput::make('likes')
                    ->label('点赞数')
                    ->required()
                    ->numeric()
                    ->default(0)
                    ->minValue(0),
                TextInput::make('comments_count')
                    ->label('评论数')
                    ->required()
                    ->numeric()
                    ->default(0)
                    ->minValue(0),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('views')
                    ->label('浏览量')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('likes')
                    ->label('点赞数')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('comments_count')
                    ->label('评论数')
                    ->numeric()
                    ->sortable(),
            ])
            ->headerActions([
                CreateAction::make(),
            ])
            ->recordActions([
                EditAction::make(),
                DeleteAction::make(),
            ]);
    }
}
