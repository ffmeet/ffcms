<x-filament-panels::page class="ecms-tags-index-page">
    @php
        $tags = $this->tags;
    @endphp

    <div class="ecms-tags-page">
        <div class="ecms-tags-header">
            <div class="ecms-tags-header-copy">
                <p class="ecms-tags-page-kicker">标签管理</p>
                <h1 class="ecms-tags-page-title">标签</h1>
            </div>

            <div class="ecms-tags-header-actions">
                @if (count($this->selectedTagIds))
                    <div class="ecms-tags-bulk-actions">
                        <span class="ecms-tags-bulk-count">已选择 {{ count($this->selectedTagIds) }} 项</span>

                        <x-filament::button size="sm" color="gray" wire:click="deleteSelected">
                            批量删除
                        </x-filament::button>

                        <x-filament::button size="sm" color="gray" wire:click="clearSelection">
                            取消
                        </x-filament::button>
                    </div>
                @endif

                <form method="GET" class="ecms-tags-filters">
                    <label class="ecms-tags-filter ecms-tags-filter-search">
                        <input type="search" name="search" value="{{ $this->search }}" placeholder="搜索标签">
                    </label>

                    <label class="ecms-tags-filter ecms-tags-filter-per-page">
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
                    :href="\App\Filament\Resources\Tags\TagResource::getUrl('create')"
                    color="primary"
                >
                    新建标签
                </x-filament::button>
            </div>
        </div>

        <div class="ecms-tags-list" role="list">
            <div class="ecms-tags-list-head" aria-hidden="true">
                <span>标签</span>
                <span>标识</span>
                <span>创建时间</span>
                <span>引用数</span>
                <span>操作</span>
            </div>

            @forelse ($tags as $tag)
                <article class="ecms-tags-list-item" role="listitem">
                    @include('filament.resources.tags.partials.tag-list-item', ['record' => $tag])
                </article>
            @empty
                <div class="ecms-tags-empty">
                    暂无标签
                </div>
            @endforelse
        </div>

        <div class="ecms-tags-footer">
            <p class="ecms-tags-results">
                Showing {{ $tags->firstItem() ?? 0 }} to {{ $tags->lastItem() ?? 0 }} of {{ $tags->total() }} results
            </p>

            <div class="ecms-tags-pagination">
                {{ $tags->onEachSide(1)->links() }}
            </div>
        </div>
    </div>
</x-filament-panels::page>
