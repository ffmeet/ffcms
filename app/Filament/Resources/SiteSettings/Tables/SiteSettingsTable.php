<?php

namespace App\Filament\Resources\SiteSettings\Tables;

use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class SiteSettingsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('site_name')
                    ->label('站点名称')
                    ->searchable(),
                TextColumn::make('site_tagline')
                    ->label('副标题')
                    ->limit(36),
                TextColumn::make('updated_at')
                    ->label('最后更新')
                    ->since(),
            ])
            ->recordActions([
                EditAction::make(),
            ]);
    }
}
