<?php

namespace App\Filament\Resources\MembershipPlans\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class MembershipPlansTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')->label('名称')->searchable(),
                TextColumn::make('billing_period')->label('计费周期'),
                TextColumn::make('price')->label('价格')->money('CNY'),
                TextColumn::make('duration_days')->label('有效天数'),
                IconColumn::make('is_active')->label('启用')->boolean(),
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
