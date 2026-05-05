<?php

namespace App\Filament\Resources\Posts\Pages;

use App\Filament\Resources\Posts\PostResource;
use App\Models\Category;
use App\Models\ContentModel;
use App\Models\Post;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Contracts\Support\Htmlable;
use Illuminate\Pagination\LengthAwarePaginator;
use Livewire\Attributes\Url;

class ListPosts extends ListRecords
{
    protected static string $resource = PostResource::class;

    protected string $view = 'filament.resources.posts.pages.list-posts';

    #[Url(as: 'view')]
    public ?string $viewMode = null;

    #[Url(as: 'status')]
    public ?string $statusFilter = null;

    #[Url(as: 'model')]
    public ?string $modelFilter = null;

    #[Url(as: 'category')]
    public ?string $categoryFilter = null;

    #[Url(as: 'perPage')]
    public int|string|null $perPage = 10;

    /**
     * @var array<int, int|string>
     */
    public array $selectedPostIds = [];

    public function mount(): void
    {
        parent::mount();

        if (blank($this->statusFilter) && $this->viewMode === 'draft') {
            $this->statusFilter = 'draft';
        }

        if (blank($this->modelFilter)) {
            $defaultModelId = match ($this->viewMode) {
                'flash' => ContentModel::query()
                    ->whereIn('table_name', Post::FLASH_MODEL_TABLE_NAMES)
                    ->orderBy('id')
                    ->value('id'),
                'draft' => null,
                default => ContentModel::query()
                    ->where('table_name', 'posts_news')
                    ->value('id'),
            };

            $this->modelFilter = filled($defaultModelId) ? (string) $defaultModelId : null;
        }

        if (! in_array((int) $this->perPage, [10, 25, 50], true)) {
            $this->perPage = 10;
        }
    }

    public function getBreadcrumbs(): array
    {
        return [];
    }

    public function getHeading(): string|Htmlable
    {
        return '';
    }

    protected function getHeaderActions(): array
    {
        return [];
    }

    public function getPostsProperty(): LengthAwarePaginator
    {
        $query = PostResource::getEloquentQuery()
            ->when(filled($this->statusFilter), fn ($query) => $query->where('status', $this->statusFilter))
            ->when(filled($this->modelFilter), fn ($query) => $query->where('model_id', $this->modelFilter))
            ->when(filled($this->categoryFilter), fn ($query) => $query->where('category_id', $this->categoryFilter));

        $this->applyDefaultOrdering($query);

        return $query
            ->paginate((int) $this->perPage)
            ->withQueryString();
    }

    protected function applyDefaultOrdering($query): void
    {
        $query
            ->orderByRaw('COALESCE(published_at, created_at) DESC')
            ->orderByDesc('created_at')
            ->orderByDesc('id');
    }

    /**
     * @return array<string, string>
     */
    public function getModelOptions(): array
    {
        return ContentModel::query()
            ->orderBy('id')
            ->pluck('name', 'id')
            ->mapWithKeys(fn ($name, $id) => [(string) $id => $name])
            ->all();
    }

    /**
     * @return array<string, string>
     */
    public function getCategoryOptions(): array
    {
        return Category::query()
            ->when(filled($this->modelFilter), fn ($query) => $query->where('model_id', $this->modelFilter))
            ->orderBy('sort_order')
            ->orderBy('id')
            ->pluck('name', 'id')
            ->mapWithKeys(fn ($name, $id) => [(string) $id => $name])
            ->all();
    }

    public function getPageHeadingText(): string
    {
        if ($this->viewMode === 'draft' && blank($this->modelFilter)) {
            return '草稿';
        }

        $modelName = filled($this->modelFilter)
            ? ContentModel::query()->whereKey($this->modelFilter)->value('name')
            : null;

        if ($this->viewMode === 'flash') {
            return filled($modelName) ? "快讯 · {$modelName}" : '快讯';
        }

        if ($this->statusFilter === 'draft' && blank($modelName)) {
            return '草稿';
        }

        return filled($modelName) ? "文章 · {$modelName}" : '文章';
    }

    public function getCreateLabel(): string
    {
        return $this->viewMode === 'flash' ? '发布快讯' : '发布文章';
    }

    public function getCreateUrl(): string
    {
        return $this->viewMode === 'flash'
            ? PostResource::getUrl('create', ['kind' => 'flash'])
            : PostResource::getUrl('create', ['kind' => 'article']);
    }

    public function clearSelection(): void
    {
        $this->selectedPostIds = [];
    }

    public function deleteSelected(): void
    {
        if ($this->selectedPostIds === []) {
            return;
        }

        Post::query()->whereIn('id', $this->selectedPostIds)->delete();

        $this->selectedPostIds = [];
    }

    public function approveSelected(): void
    {
        if ($this->selectedPostIds === []) {
            return;
        }

        $selectedIds = $this->selectedPostIds;

        Post::query()
            ->whereIn('id', $selectedIds)
            ->where('status', '!=', 'published')
            ->get()
            ->each(function (Post $post): void {
                $post->update([
                    'status' => 'published',
                    'published_at' => $post->published_at ?? now(),
                ]);
            });

        $approvedCount = Post::query()
            ->whereIn('id', $selectedIds)
            ->where('status', 'published')
            ->count();

        $this->selectedPostIds = [];

        Notification::make()
            ->success()
            ->title('批量审核完成')
            ->body("已处理 {$approvedCount} 篇文章。")
            ->send();
    }
}
