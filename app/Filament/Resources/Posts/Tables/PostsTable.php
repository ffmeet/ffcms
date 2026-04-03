<?php

namespace App\Filament\Resources\Posts\Tables;

use App\Filament\Resources\Posts\PostResource;
use App\Models\ContentModel;
use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Tables\Columns\ViewColumn;
use Filament\Tables\Enums\FiltersLayout;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class PostsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->extraAttributes(['class' => 'ecms-posts-table'])
            ->heading(static::resolveHeading())
            ->columns([
                ViewColumn::make('post_card')
                    ->label('')
                    ->view('filament.tables.columns.post-card'),
            ])
            ->filters([
                SelectFilter::make('status')
                    ->label('状态')
                    ->placeholder('所有状态')
                    ->options([
                        'draft' => '草稿',
                        'pending' => '待审核',
                        'published' => '已发布',
                    ]),
                SelectFilter::make('model_id')
                    ->label('模型')
                    ->placeholder('所有模型')
                    ->options(ContentModel::query()->orderBy('id')->pluck('name', 'id')->all())
                    ->query(fn ($query, array $data) => filled($data['value'] ?? null)
                        ? $query->where('model_id', $data['value'])
                        : $query),
                SelectFilter::make('category')
                    ->label('栏目')
                    ->placeholder('所有栏目')
                    ->relationship('category', 'name'),
            ], layout: FiltersLayout::AboveContent)
            ->deferFilters(false)
            ->filtersFormColumns(3)
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ])
                    ->label('批量处理'),
                Action::make('createCurrentModel')
                    ->label(request()->query('view') === 'flash' ? '发布快讯' : '发布文章')
                    ->url(request()->query('view') === 'flash'
                        ? PostResource::getUrl('create', ['kind' => 'flash'])
                        : PostResource::getUrl('create', ['kind' => 'article']))
                    ->button()
                    ->extraAttributes(['class' => 'ecms-posts-publish-action']),
            ]);
    }

    protected static function resolveHeading(): string
    {
        $view = request()->query('view');

        if ($view === 'flash') {
            $modelName = ContentModel::query()
                ->whereIn('table_name', \App\Models\Post::FLASH_MODEL_TABLE_NAMES)
                ->orderBy('id')
                ->value('name');

            return filled($modelName) ? "快讯 · {$modelName}" : '快讯';
        }

        if ($view === 'draft') {
            return '草稿';
        }

        $modelId = data_get(request()->query(), 'tableFilters.model_id.value');

        if (filled($modelId)) {
            $modelName = ContentModel::query()->whereKey($modelId)->value('name');

            return filled($modelName) ? "文章 · {$modelName}" : '文章';
        }

        $newsModelName = ContentModel::query()
            ->where('table_name', 'posts_news')
            ->value('name');

        return filled($newsModelName) ? "文章 · {$newsModelName}" : '文章';
    }
}
