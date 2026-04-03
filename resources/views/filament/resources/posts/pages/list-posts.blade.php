<x-filament-panels::page class="ecms-posts-index-page">
    @php
        $posts = $this->posts;
        $modelOptions = $this->getModelOptions();
        $categoryOptions = $this->getCategoryOptions();
    @endphp

    <div class="ecms-posts-page">
        <div class="ecms-posts-header">
            <div class="ecms-posts-header-copy">
                <h1 class="ecms-posts-page-title">{{ $this->getPageHeadingText() }}</h1>
            </div>

            <div class="ecms-posts-header-actions">
                @if (count($this->selectedPostIds))
                    <div class="ecms-posts-bulk-actions">
                        <span class="ecms-posts-bulk-count">已选择 {{ count($this->selectedPostIds) }} 项</span>

                        <x-filament::button size="sm" color="gray" wire:click="deleteSelected">
                            批量删除
                        </x-filament::button>

                        <x-filament::button size="sm" color="gray" wire:click="clearSelection">
                            取消
                        </x-filament::button>
                    </div>
                @endif

                <form method="GET" class="ecms-posts-filters">
                    @if (filled($this->viewMode))
                        <input type="hidden" name="view" value="{{ $this->viewMode }}">
                    @endif

                    <label class="ecms-posts-filter">
                        <select name="status" onchange="this.form.submit()">
                            <option value="">所有状态</option>
                            <option value="draft" @selected($this->statusFilter === 'draft')>草稿</option>
                            <option value="pending" @selected($this->statusFilter === 'pending')>待审核</option>
                            <option value="published" @selected($this->statusFilter === 'published')>已发布</option>
                        </select>
                    </label>

                    <label class="ecms-posts-filter">
                        <select name="model" onchange="this.form.submit()">
                            <option value="">所有模型</option>
                            @foreach ($modelOptions as $value => $label)
                                <option value="{{ $value }}" @selected((string) $this->modelFilter === (string) $value)>
                                    {{ $label }}
                                </option>
                            @endforeach
                        </select>
                    </label>

                    <label class="ecms-posts-filter">
                        <select name="category" onchange="this.form.submit()">
                            <option value="">所有栏目</option>
                            @foreach ($categoryOptions as $value => $label)
                                <option value="{{ $value }}" @selected((string) $this->categoryFilter === (string) $value)>
                                    {{ $label }}
                                </option>
                            @endforeach
                        </select>
                    </label>

                    <label class="ecms-posts-filter ecms-posts-filter-per-page">
                        <select name="perPage" onchange="this.form.submit()">
                            @foreach ([10, 25, 50] as $size)
                                <option value="{{ $size }}" @selected((int) $this->perPage === $size)>{{ $size }} / 页</option>
                            @endforeach
                        </select>
                    </label>
                </form>

                <x-filament::button
                    tag="a"
                    :href="$this->getCreateUrl()"
                    color="primary"
                >
                    {{ $this->getCreateLabel() }}
                </x-filament::button>
            </div>
        </div>

        <div class="ecms-posts-list" role="list">
            @forelse ($posts as $post)
                <article class="ecms-posts-list-item" role="listitem">
                    @include('filament.resources.posts.partials.post-list-item', ['record' => $post])
                </article>
            @empty
                <div class="ecms-posts-empty">
                    暂无文章
                </div>
            @endforelse
        </div>

        <div class="ecms-posts-footer">
            <p class="ecms-posts-results">
                Showing {{ $posts->firstItem() ?? 0 }} to {{ $posts->lastItem() ?? 0 }} of {{ $posts->total() }} results
            </p>

            <div class="ecms-posts-pagination">
                {{ $posts->onEachSide(1)->links() }}
            </div>
        </div>
    </div>
</x-filament-panels::page>
