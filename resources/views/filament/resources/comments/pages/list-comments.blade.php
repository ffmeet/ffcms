<x-filament-panels::page class="ecms-comments-index-page">
    @php
        $comments = $this->comments;
    @endphp

    <div class="ecms-comments-page">
        <div class="ecms-comments-header">
            <div class="ecms-comments-header-copy">
                <h1 class="ecms-comments-page-title">评论</h1>
            </div>

            <div class="ecms-comments-header-actions">
                @if (count($this->selectedCommentIds))
                    <div class="ecms-comments-bulk-actions">
                        <span class="ecms-comments-bulk-count">已选择 {{ count($this->selectedCommentIds) }} 项</span>

                        <x-filament::button size="sm" color="success" wire:click="approveSelected">
                            批量通过
                        </x-filament::button>

                        <x-filament::button size="sm" color="danger" wire:click="rejectSelected">
                            批量驳回
                        </x-filament::button>

                        <x-filament::button size="sm" color="gray" wire:click="deleteSelected">
                            批量删除
                        </x-filament::button>

                        <x-filament::button size="sm" color="gray" wire:click="clearSelection">
                            取消
                        </x-filament::button>
                    </div>
                @endif

                <form method="GET" class="ecms-comments-filters">
                    <label class="ecms-comments-filter">
                        <select name="status" onchange="this.form.submit()">
                            <option value="">所有状态</option>
                            <option value="pending" @selected($this->statusFilter === 'pending')>待审核</option>
                            <option value="approved" @selected($this->statusFilter === 'approved')>已通过</option>
                            <option value="rejected" @selected($this->statusFilter === 'rejected')>已驳回</option>
                        </select>
                    </label>

                    <label class="ecms-comments-filter ecms-comments-filter-per-page">
                        <select name="perPage" onchange="this.form.submit()">
                            @foreach ([10, 25, 50] as $size)
                                <option value="{{ $size }}" @selected((int) $this->perPage === $size)>{{ $size }} / 页</option>
                            @endforeach
                        </select>
                    </label>
                </form>
            </div>
        </div>

        <div class="ecms-comments-list" role="list">
            @forelse ($comments as $comment)
                <article class="ecms-comments-list-item" role="listitem">
                    @include('filament.resources.comments.partials.comment-list-item', ['record' => $comment])
                </article>
            @empty
                <div class="ecms-comments-empty">
                    暂无评论
                </div>
            @endforelse
        </div>

        <div class="ecms-comments-footer">
            <p class="ecms-comments-results">
                Showing {{ $comments->firstItem() ?? 0 }} to {{ $comments->lastItem() ?? 0 }} of {{ $comments->total() }} results
            </p>

            <div class="ecms-comments-pagination">
                {{ $comments->onEachSide(1)->links() }}
            </div>
        </div>
    </div>
</x-filament-panels::page>
