<?php

namespace App\Filament\Resources\Users\Pages;

use App\Filament\Resources\Users\UserResource;
use App\Models\MemberGroup;
use App\Models\User;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Contracts\Support\Htmlable;
use Illuminate\Pagination\LengthAwarePaginator;
use Livewire\Attributes\Url;

class ListUsers extends ListRecords
{
    protected static string $resource = UserResource::class;

    protected string $view = 'filament.resources.users.pages.list-users';

    #[Url(as: 'search')]
    public ?string $search = null;

    #[Url(as: 'status')]
    public ?string $statusFilter = null;

    #[Url(as: 'group')]
    public ?string $groupFilter = null;

    #[Url(as: 'perPage')]
    public int|string|null $perPage = 10;

    /**
     * @var array<int, int|string>
     */
    public array $selectedUserIds = [];

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

    public function getUsersProperty(): LengthAwarePaginator
    {
        return User::query()
            ->with('memberGroup')
            ->withCount([
                'posts',
                'comments',
                'subscriptions as active_subscriptions_count' => fn ($query) => $query->where('status', 'active'),
            ])
            ->when(filled($this->search), function ($query): void {
                $query->where(function ($nested): void {
                    $nested
                        ->where('username', 'like', '%' . $this->search . '%')
                        ->orWhere('email', 'like', '%' . $this->search . '%');
                });
            })
            ->when(filled($this->statusFilter), fn ($query) => $query->where('status', $this->statusFilter))
            ->when(filled($this->groupFilter), fn ($query) => $query->where('group_id', $this->groupFilter))
            ->latest('id')
            ->paginate((int) $this->perPage)
            ->withQueryString();
    }

    /**
     * @return array<string, string>
     */
    public function getGroupOptions(): array
    {
        return MemberGroup::query()
            ->orderBy('id')
            ->pluck('name', 'id')
            ->mapWithKeys(fn ($name, $id) => [(string) $id => $name])
            ->all();
    }

    public function clearSelection(): void
    {
        $this->selectedUserIds = [];
    }

    public function deleteSelected(): void
    {
        if ($this->selectedUserIds === []) {
            return;
        }

        User::query()->whereIn('id', $this->selectedUserIds)->delete();

        $this->selectedUserIds = [];
    }
}
