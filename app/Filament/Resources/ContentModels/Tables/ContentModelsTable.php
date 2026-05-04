<?php

namespace App\Filament\Resources\ContentModels\Tables;

use App\Models\Post;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Enums\FiltersLayout;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class ContentModelsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->heading('内容模型台')
            ->defaultSort('id', 'desc')
            ->columns([
                TextColumn::make('name')
                    ->label('模型名称')
                    ->searchable(),
                TextColumn::make('table_name')
                    ->label('逻辑表名')
                    ->searchable(),
                TextColumn::make('posts_count')
                    ->label('内容数')
                    ->counts('posts')
                    ->sortable(),
                TextColumn::make('field_count')
                    ->label('字段数')
                    ->state(fn ($record): int => count($record->normalizedFieldConfig()))
                    ->sortable(false),
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
                SelectFilter::make('model_family')
                    ->label('模型类型')
                    ->placeholder('全部模型')
                    ->options([
                        'article' => '文章模型',
                        'flash' => '快讯模型',
                    ])
                    ->query(function ($query, array $data) {
                        return match ($data['value'] ?? null) {
                            'flash' => $query->whereIn('table_name', Post::FLASH_MODEL_TABLE_NAMES),
                            'article' => $query->whereNotIn('table_name', Post::FLASH_MODEL_TABLE_NAMES),
                            default => $query,
                        };
                    }),
                SelectFilter::make('field_setup')
                    ->label('字段配置')
                    ->placeholder('全部状态')
                    ->options([
                        'configured' => '已配置字段',
                        'empty' => '未配置字段',
                    ])
                    ->query(function ($query, array $data) {
                        return match ($data['value'] ?? null) {
                            'configured' => $query->where('field_config', '!=', '[]')->whereNotNull('field_config'),
                            'empty' => $query->where(function ($subQuery) {
                                $subQuery->whereNull('field_config')
                                    ->orWhere('field_config', '[]');
                            }),
                            default => $query,
                        };
                    }),
            ], layout: FiltersLayout::AboveContent)
            ->deferFilters(false)
            ->filtersFormColumns(2)
            ->emptyStateHeading('还没有内容模型')
            ->emptyStateDescription('先定义文章、快讯等内容模型，再把扩展字段配置给编辑和前台投稿链路使用。')
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
