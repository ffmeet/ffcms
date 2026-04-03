<?php

namespace App\Filament\Resources\Posts\Pages;

use App\Filament\Resources\Posts\PostResource;
use App\Models\Tag;
use App\Support\ContentModelFieldManager;
use Filament\Support\Enums\Alignment;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;
use Filament\Resources\Pages\CreateRecord;

class CreatePost extends CreateRecord
{
    protected static string $resource = PostResource::class;

    public string $kind = 'article';

    public function mount(): void
    {
        $kind = request()->route('kind');

        $this->kind = in_array($kind, ['article', 'flash'], true) ? $kind : 'article';

        parent::mount();

        $modelId = \App\Models\ContentModel::query()
            ->where('table_name', $this->kind === 'flash' ? 'posts_flash' : 'posts_news')
            ->value('id');

        $categoryId = \App\Models\Category::query()
            ->where('model_id', $modelId)
            ->orderBy('sort_order')
            ->orderBy('id')
            ->value('id');

        $this->form->fill([
            ...($this->data ?? []),
            'post_kind' => $this->kind,
            'model_id' => $modelId,
            'category_id' => $categoryId,
            'user_id' => auth()->id(),
            'status' => 'published',
            'published_at' => now(),
        ]);
    }

    public function getTitle(): string
    {
        return $this->getCreateKind() === 'flash' ? '发布快讯' : '发布文章';
    }

    public function getFormActionsAlignment(): string | Alignment
    {
        return Alignment::End;
    }

    protected function getFormActions(): array
    {
        return [];
    }

    public function publishFromSidebar(): void
    {
        $this->data['status'] = 'published';

        $this->create();
    }

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        unset($data['post_kind']);

        $data['model_id'] = static::getModel()::ensureCategoryMatchesModel(
            $data['category_id'] ?? null,
            $data['model_id'] ?? null,
        );

        return static::getModel()::normalizePublishingDataForRecord($data);
    }

    protected function handleRecordCreation(array $data): Model
    {
        $customFields = ContentModelFieldManager::filterCustomFieldsForModelId(
            $data['custom_fields'] ?? [],
            $data['model_id'] ?? null,
        );
        $customFields['seo_title'] = $data['seo_title'] ?? null;
        $customFields['summary'] = $data['summary'] ?? null;
        $customFields['author_name'] = $data['author_name'] ?? null;
        $tagIds = $this->resolveTagIds($data['tag_names'] ?? []);

        $detailData = [
            'content' => $data['content'] ?? null,
            'custom_fields' => $customFields,
        ];

        unset($data['content'], $data['custom_fields'], $data['coverMediaFiles'], $data['attachmentMediaFiles'], $data['seo_title'], $data['summary'], $data['author_name'], $data['tag_names']);

        /** @var Model $record */
        $record = static::getModel()::create($data);

        if ($record->isFlashModel()) {
            $record->update([
                'slug' => $record::generateFlashSlugForCategory($record->category_id, $record->id),
            ]);
        }

        $record->detail()->create($detailData);
        $record->tags()->sync($tagIds);
        $this->syncTagCounts();
        $record->syncCommentStatistics();
        $record->statistics()->firstOrCreate(
            ['post_id' => $record->id],
            ['views' => 0, 'likes' => 0, 'comments_count' => 0],
        );

        return $record;
    }

    protected function getCreateKind(): string
    {
        return $this->kind;
    }

    /**
     * @param  array<int, string>  $tagNames
     * @return array<int, int>
     */
    protected function resolveTagIds(array $tagNames): array
    {
        return collect($tagNames)
            ->map(fn (mixed $tag): string => trim((string) $tag))
            ->filter()
            ->unique()
            ->take(12)
            ->map(function (string $tag): int {
                $slug = Str::slug($tag);
                $slug = filled($slug) ? $slug : 'tag-' . substr(md5($tag), 0, 12);

                return Tag::query()->firstOrCreate(
                    ['slug' => $slug],
                    ['name' => $tag, 'count' => 0],
                )->id;
            })
            ->all();
    }

    protected function syncTagCounts(): void
    {
        Tag::query()
            ->get()
            ->each(fn (Tag $tag) => $tag->update(['count' => $tag->posts()->count()]));
    }
}
