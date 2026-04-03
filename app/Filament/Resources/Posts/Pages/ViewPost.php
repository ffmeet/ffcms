<?php

namespace App\Filament\Resources\Posts\Pages;

use App\Filament\Resources\Posts\PostResource;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Group;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Text;
use Filament\Schemas\Schema;

class ViewPost extends ViewRecord
{
    protected static string $resource = PostResource::class;

    protected function getHeaderActions(): array
    {
        return [
            EditAction::make(),
        ];
    }

    public function content(Schema $schema): Schema
    {
        $record = $this->getRecord();

        $statusLabel = match ($record->status) {
            'draft' => '草稿',
            'pending' => '待审核',
            'published' => '已发布',
            default => $record->status,
        };

        return $schema
            ->components([
                Grid::make([
                    'default' => 1,
                    'xl' => 12,
                ])->schema([
                    Group::make([
                        $this->getInfolistContentComponent(),
                        Section::make('评论与统计')
                            ->contained(false)
                            ->schema([
                                $this->getRelationManagersContentComponent(),
                            ]),
                    ])->columnSpan([
                        'xl' => 8,
                    ]),
                    Group::make([
                        Section::make('文章信息')
                            ->description('右栏只保留整理过的辅助信息，不再和正文混在一起。')
                            ->schema([
                                Text::make('栏目')
                                    ->weight('medium'),
                                Text::make($record->category?->name ?? '未分类'),
                                Text::make('内容模型')
                                    ->weight('medium'),
                                Text::make($record->contentModel?->name ?? '未设置'),
                                Text::make('作者')
                                    ->weight('medium'),
                                Text::make($record->display_author),
                                Text::make('状态')
                                    ->weight('medium'),
                                Text::make($statusLabel),
                                Text::make('发布时间')
                                    ->weight('medium'),
                                Text::make(optional($record->published_at)->format('Y-m-d H:i') ?: '未发布'),
                                Text::make('别名')
                                    ->weight('medium'),
                                Text::make($record->slug)->copyable(),
                            ])
                            ->columns(1),
                        Section::make('内容补充')
                            ->schema([
                                Text::make('标签')
                                    ->weight('medium'),
                                Text::make(
                                    $record->tags->pluck('name')->filter()->isNotEmpty()
                                        ? $record->tags->pluck('name')->filter()->join(' / ')
                                        : '暂无标签'
                                ),
                                Text::make('附件数量')
                                    ->weight('medium'),
                                Text::make((string) $record->attachmentMediaFiles->count()),
                                Text::make('评论数')
                                    ->weight('medium'),
                                Text::make((string) ($record->statistics?->comments_count ?? 0)),
                            ])
                            ->columns(1),
                    ])->columnSpan([
                        'xl' => 4,
                    ]),
                ]),
            ]);
    }
}
