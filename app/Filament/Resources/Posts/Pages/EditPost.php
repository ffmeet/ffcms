<?php

namespace App\Filament\Resources\Posts\Pages;

use App\Filament\Resources\Posts\PostResource;
use App\Models\Tag;
use App\Support\ContentModelFieldManager;
use Filament\Actions\Action;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;
use Filament\Support\Enums\Alignment;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Arr;
use Illuminate\Support\HtmlString;
use Illuminate\Support\Str;

class EditPost extends EditRecord
{
    protected static string $resource = PostResource::class;

    public function getRelationManagers(): array
    {
        return [];
    }

    public function getFormActionsAlignment(): string | Alignment
    {
        return Alignment::End;
    }

    public function publishFromSidebar(): void
    {
        $this->data['status'] = 'published';

        $this->save();
    }

    protected function mutateFormDataBeforeFill(array $data): array
    {
        $data['post_kind'] = $this->record->isFlashModel() ? 'flash' : 'article';
        $data['content'] = $this->normalizeEditorContent($this->record->detail?->content);
        $customFields = $this->record->detail?->custom_fields ?? [];
        $data['seo_title'] = $customFields['seo_title'] ?? null;
        $data['summary'] = $customFields['summary'] ?? null;
        $data['author_name'] = $customFields['author_name'] ?? null;
        $data['tag_names'] = $this->record->tags()->orderBy('name')->pluck('name')->all();
        unset($customFields['seo_title'], $customFields['summary'], $customFields['author_name']);
        $data['custom_fields'] = ContentModelFieldManager::filterCustomFieldsForModelId(
            $customFields,
            $data['model_id'] ?? $this->record->model_id,
        );

        return $data;
    }

    protected function normalizeEditorContent(mixed $content): array|string|null
    {
        if ($content instanceof HtmlString) {
            return $content->toHtml();
        }

        if (blank($content)) {
            return null;
        }

        if (is_array($content) && Arr::has($content, 'type')) {
            return $content;
        }

        if (is_string($content)) {
            $trimmed = trim($content);

            if ($trimmed === '') {
                return null;
            }

            if (str_starts_with($trimmed, '<')) {
                return $trimmed;
            }

            $decoded = json_decode($trimmed, true);

            if (json_last_error() === JSON_ERROR_NONE && is_array($decoded) && Arr::has($decoded, 'type')) {
                return $decoded;
            }

            return [
                'type' => 'doc',
                'content' => [[
                    'type' => 'paragraph',
                    'content' => [[
                        'type' => 'text',
                        'text' => $trimmed,
                    ]],
                ]],
            ];
        }

        return [
            'type' => 'doc',
            'content' => [[
                'type' => 'paragraph',
                'content' => [[
                    'type' => 'text',
                    'text' => (string) $content,
                ]],
            ]],
        ];
    }

    protected function mutateFormDataBeforeSave(array $data): array
    {
        $data['model_id'] = $this->record::ensureCategoryMatchesModel(
            $data['category_id'] ?? null,
            $data['model_id'] ?? $this->record->model_id,
        );

        return $this->record::normalizePublishingDataForRecord($data, $this->record->getKey());
    }

    protected function handleRecordUpdate(Model $record, array $data): Model
    {
        unset($data['post_kind']);

        $customFields = ContentModelFieldManager::filterCustomFieldsForModelId(
            $data['custom_fields'] ?? [],
            $data['model_id'] ?? $record->model_id,
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

        $record->update($data);
        $record::syncEditorialPlacementsForRecord($record);
        $record->refresh();

        if ($record->isFlashModel()) {
            $record->update([
                'slug' => $record::generateFlashSlugForCategory($record->category_id, $record->id),
            ]);
        }

        $record->detail()->updateOrCreate(['post_id' => $record->id], $detailData);
        $record->tags()->sync($tagIds);
        $this->syncTagCounts();

        return $record;
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

    protected function getHeaderActions(): array
    {
        return [
            Action::make('preview')
                ->label('前台预览')
                ->icon('heroicon-o-arrow-top-right-on-square')
                ->color('gray')
                ->outlined()
                ->extraAttributes(['class' => 'ecms-soft-header-action'])
                ->url(fn (): string => $this->record->public_url)
                ->openUrlInNewTab(),
            DeleteAction::make()
                ->label('删除')
                ->color('gray')
                ->outlined()
                ->extraAttributes(['class' => 'ecms-soft-header-action']),
        ];
    }
}
