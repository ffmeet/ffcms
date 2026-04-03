<?php

namespace App\Filament\Resources\Comments\Pages;

use App\Filament\Resources\Comments\CommentResource;
use App\Models\Comment;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Contracts\Support\Htmlable;
use Illuminate\Pagination\LengthAwarePaginator;
use Livewire\Attributes\Url;

class ListComments extends ListRecords
{
    protected static string $resource = CommentResource::class;

    protected string $view = 'filament.resources.comments.pages.list-comments';

    #[Url(as: 'status')]
    public ?string $statusFilter = null;

    #[Url(as: 'perPage')]
    public int|string|null $perPage = 10;

    /**
     * @var array<int, int|string>
     */
    public array $selectedCommentIds = [];

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

    public function getCommentsProperty(): LengthAwarePaginator
    {
        return CommentResource::getEloquentQuery()
            ->when(filled($this->statusFilter), fn ($query) => $query->where('status', $this->statusFilter))
            ->latest('id')
            ->paginate((int) $this->perPage)
            ->withQueryString();
    }

    public function approveSelected(): void
    {
        $this->bulkUpdateStatus('approved');
    }

    public function rejectSelected(): void
    {
        $this->bulkUpdateStatus('rejected');
    }

    public function deleteSelected(): void
    {
        if ($this->selectedCommentIds === []) {
            return;
        }

        Comment::query()
            ->whereIn('id', $this->selectedCommentIds)
            ->get()
            ->each
            ->delete();

        $this->selectedCommentIds = [];
    }

    public function clearSelection(): void
    {
        $this->selectedCommentIds = [];
    }

    protected function bulkUpdateStatus(string $status): void
    {
        if ($this->selectedCommentIds === []) {
            return;
        }

        Comment::query()
            ->whereIn('id', $this->selectedCommentIds)
            ->update(['status' => $status]);

        $this->selectedCommentIds = [];
    }
}
