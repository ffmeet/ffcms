<?php

namespace App\Filament\Resources\Tags\Pages;

use App\Filament\Resources\Tags\TagResource;
use App\Models\Tag;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Contracts\Support\Htmlable;
use Illuminate\Pagination\LengthAwarePaginator;
use Livewire\Attributes\Url;

class ListTags extends ListRecords
{
    protected static string $resource = TagResource::class;

    protected string $view = 'filament.resources.tags.pages.list-tags';

    #[Url(as: 'search')]
    public ?string $search = null;

    #[Url(as: 'perPage')]
    public int|string|null $perPage = 10;

    /**
     * @var array<int, int|string>
     */
    public array $selectedTagIds = [];

    public function mount(): void
    {
        parent::mount();

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

    public function getTagsProperty(): LengthAwarePaginator
    {
        return Tag::query()
            ->when(filled($this->search), function ($query): void {
                $query->where(function ($nested): void {
                    $nested
                        ->where('name', 'like', '%' . $this->search . '%')
                        ->orWhere('slug', 'like', '%' . $this->search . '%');
                });
            })
            ->latest('id')
            ->paginate((int) $this->perPage)
            ->withQueryString();
    }

    public function clearSelection(): void
    {
        $this->selectedTagIds = [];
    }

    public function deleteSelected(): void
    {
        if ($this->selectedTagIds === []) {
            return;
        }

        Tag::query()->whereIn('id', $this->selectedTagIds)->delete();

        $this->selectedTagIds = [];
    }
}
