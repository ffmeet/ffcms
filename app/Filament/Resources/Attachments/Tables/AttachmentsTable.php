<?php

namespace App\Filament\Resources\Attachments\Tables;

use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class AttachmentsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                ImageColumn::make('url')
                    ->label('预览')
                    ->circular()
                    ->defaultImageUrl(fn ($record): ?string => $record->is_image ? $record->url : null)
                    ->visible(fn ($record): bool => $record->is_image),
                TextColumn::make('user.id')
                    ->label('上传用户')
                    ->searchable(),
                TextColumn::make('filename')
                    ->label('文件名')
                    ->searchable(),
                TextColumn::make('readable_size')
                    ->label('大小')
                    ->badge()
                    ->color('gray'),
                TextColumn::make('filepath')
                    ->label('存储路径')
                    ->limit(40)
                    ->searchable(),
                TextColumn::make('mime_type')
                    ->searchable(),
                TextColumn::make('size')
                    ->label('大小')
                    ->numeric()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                //
            ])
            ->recordActions([
                Action::make('open')
                    ->label('查看文件')
                    ->icon('heroicon-o-arrow-top-right-on-square')
                    ->url(fn ($record): ?string => $record?->url)
                    ->openUrlInNewTab()
                    ->visible(fn ($record): bool => filled($record?->url)),
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
