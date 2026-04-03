<x-filament-panels::page class="ecms-users-index-page">
    @php
        $users = $this->users;
        $groupOptions = $this->getGroupOptions();
    @endphp

    <div class="ecms-users-page">
        <div class="ecms-users-header">
            <div class="ecms-users-header-copy">
                <p class="ecms-users-page-kicker">会员列表</p>
                <h1 class="ecms-users-page-title">会员</h1>
            </div>

            <div class="ecms-users-header-actions">
                @if (count($this->selectedUserIds))
                    <div class="ecms-users-bulk-actions">
                        <span class="ecms-users-bulk-count">已选择 {{ count($this->selectedUserIds) }} 项</span>

                        <x-filament::button size="sm" color="gray" wire:click="deleteSelected">
                            批量删除
                        </x-filament::button>

                        <x-filament::button size="sm" color="gray" wire:click="clearSelection">
                            取消
                        </x-filament::button>
                    </div>
                @endif

                <form method="GET" class="ecms-users-filters">
                    <label class="ecms-users-filter ecms-users-filter-search">
                        <input type="search" name="search" value="{{ $this->search }}" placeholder="搜索会员">
                    </label>

                    <label class="ecms-users-filter">
                        <select name="status" onchange="this.form.submit()">
                            <option value="">所有状态</option>
                            <option value="active" @selected($this->statusFilter === 'active')>正常</option>
                            <option value="inactive" @selected($this->statusFilter === 'inactive')>停用</option>
                        </select>
                    </label>

                    <label class="ecms-users-filter">
                        <select name="group" onchange="this.form.submit()">
                            <option value="">所有分组</option>
                            @foreach ($groupOptions as $value => $label)
                                <option value="{{ $value }}" @selected((string) $this->groupFilter === (string) $value)>{{ $label }}</option>
                            @endforeach
                        </select>
                    </label>

                    <label class="ecms-users-filter ecms-users-filter-per-page">
                        <select name="perPage" onchange="this.form.submit()">
                            @foreach ([10, 25, 50] as $size)
                                <option value="{{ $size }}" @selected((int) $this->perPage === $size)>{{ $size }} / 页</option>
                            @endforeach
                        </select>
                    </label>

                    <x-filament::button type="submit" color="gray">
                        筛选
                    </x-filament::button>
                </form>

                <x-filament::button
                    tag="a"
                    :href="\App\Filament\Resources\Users\UserResource::getUrl('create')"
                    color="primary"
                >
                    新建会员
                </x-filament::button>
            </div>
        </div>

        <div class="ecms-users-list" role="list">
            <div class="ecms-users-list-head" aria-hidden="true">
                <span>会员</span>
                <span>状态</span>
                <span>级别</span>
                <span>操作</span>
                <span>文章</span>
                <span>评论</span>
            </div>

            @forelse ($users as $user)
                <article class="ecms-users-list-item" role="listitem">
                    @include('filament.resources.users.partials.user-list-item', ['record' => $user])
                </article>
            @empty
                <div class="ecms-users-empty">
                    暂无会员
                </div>
            @endforelse
        </div>

        <div class="ecms-users-footer">
            <p class="ecms-users-results">
                Showing {{ $users->firstItem() ?? 0 }} to {{ $users->lastItem() ?? 0 }} of {{ $users->total() }} results
            </p>

            <div class="ecms-users-pagination">
                {{ $users->onEachSide(1)->links() }}
            </div>
        </div>
    </div>
</x-filament-panels::page>
