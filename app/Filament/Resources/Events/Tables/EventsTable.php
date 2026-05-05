<?php

namespace App\Filament\Resources\Events\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class EventsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('title')->label('标题')->searchable(),
                TextColumn::make('status')->label('状态'),
                TextColumn::make('location')->label('地点')->limit(18),
                TextColumn::make('starts_at')->label('开始时间')->dateTime('Y-m-d H:i'),
                TextColumn::make('price')->label('价格')->money('CNY'),
            ])
            ->recordActions([
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
