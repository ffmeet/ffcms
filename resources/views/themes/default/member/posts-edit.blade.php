@extends(\App\Support\SiteTheme::view('layout', 'themes.default.layout'), ['title' => '编辑稿件'])

@section('content')
    @include(\App\Support\SiteTheme::view('member.partials.topbar', 'site.member.partials.topbar'))

    @php
        $selectedCoverAttachment = $coverAttachments->firstWhere('id', (int) old('cover_attachment_id', $post->cover_attachment_id));
        $coverPreviewUrl = $selectedCoverAttachment?->url ?? $post->cover_image_url;
        $coverPreviewLabel = $selectedCoverAttachment
            ? '当前将使用你在媒体库中选择的图片作为封面。'
            : (filled($post->cover_image_url) ? '当前稿件会继续使用这张封面图片。' : null);
    @endphp

    <div class="grid gap-6 lg:grid-cols-[320px_minmax(0,1fr)]">
        @include(\App\Support\SiteTheme::view('member.partials.nav', 'site.member.partials.nav'))

        <div class="space-y-6">
            <section class="rounded-[28px] border border-stone-200/70 bg-white/85 p-6 shadow-sm backdrop-blur">
                <div class="flex flex-col gap-4 md:flex-row md:items-end md:justify-between">
                    <div>
                        <p class="text-xs font-semibold uppercase tracking-[0.3em] text-amber-600">Edit Post</p>
                        <h1 class="mt-2 text-3xl font-semibold tracking-tight text-stone-900">编辑稿件</h1>
                    </div>
                    <a href="{{ route('member.posts.index') }}" class="inline-flex items-center justify-center rounded-full border border-stone-200 px-4 py-2 text-sm font-medium text-stone-700 transition hover:border-amber-400 hover:text-amber-700">返回我的稿件</a>
                </div>
            </section>

            <form method="POST" action="{{ route('member.posts.update', $post) }}" enctype="multipart/form-data" class="grid gap-6 xl:grid-cols-[minmax(0,1fr)_320px]">
                @csrf
                @method('PUT')

                <section class="space-y-6">
                    <div class="rounded-[28px] border border-stone-200/70 bg-white/90 p-6 shadow-sm">
                        <label for="title" class="text-sm font-medium text-stone-700">标题</label>
                        <input id="title" name="title" type="text" value="{{ old('title', $post->title) }}" required class="mt-3 w-full rounded-2xl border border-stone-200 bg-stone-50 px-4 py-3 text-base text-stone-900 outline-none transition focus:border-amber-400 focus:bg-white">

                        <label for="summary" class="mt-5 block text-sm font-medium text-stone-700">摘要</label>
                        <textarea id="summary" name="summary" rows="4" class="mt-3 w-full rounded-2xl border border-stone-200 bg-stone-50 px-4 py-3 text-sm text-stone-900 outline-none transition focus:border-amber-400 focus:bg-white">{{ old('summary', $post->summary) }}</textarea>

                        <label for="content" class="mt-5 block text-sm font-medium text-stone-700">正文</label>
                        <textarea id="content" name="content" rows="9" required class="mt-3 w-full rounded-[24px] border border-stone-200 bg-stone-50 px-4 py-4 text-sm leading-7 text-stone-900 outline-none transition focus:border-amber-400 focus:bg-white">{{ old('content', $post->detail?->content) }}</textarea>

                        <div class="mt-5 flex flex-col gap-4 sm:flex-row sm:items-start" data-cover-picker>
                            <div class="rounded-[20px] border border-stone-200 bg-stone-50 p-4 sm:min-w-0 sm:flex-1" data-cover-preview>
                                <p class="text-sm font-medium text-stone-800">当前封面预览</p>

                                @if (filled($coverPreviewUrl))
                                    <div class="mt-3 overflow-hidden rounded-[18px] border border-stone-200 bg-white" data-cover-preview-filled>
                                        <div class="aspect-[16/9] bg-stone-100">
                                            <img src="{{ $coverPreviewUrl }}" alt="封面预览" class="h-full w-full object-cover" data-cover-preview-image>
                                        </div>
                                        <div class="space-y-1 border-t border-stone-100 px-4 py-3">
                                            <p class="text-sm text-stone-700" data-cover-preview-label>{{ $coverPreviewLabel }}</p>
                                            <p class="break-all text-xs text-stone-500" data-cover-preview-meta>{{ $coverPreviewUrl }}</p>
                                        </div>
                                    </div>
                                @else
                                    <p class="mt-3 rounded-2xl border border-dashed border-stone-300 px-4 py-4 text-sm leading-6 text-stone-500" data-cover-preview-empty>当前还没有选定封面。</p>
                                @endif
                            </div>

                            <div class="relative flex flex-col items-stretch gap-3 pt-1 sm:w-[156px] sm:flex-none">
                                <label for="cover_upload" class="inline-flex w-full cursor-pointer items-center justify-center border border-sky-200 bg-sky-50 px-4 py-2.5 text-sm font-medium text-sky-700 transition hover:border-sky-300 hover:bg-sky-100">选择文件</label>
                                <button type="button" data-toggle-cover-library class="inline-flex w-full items-center justify-center border border-stone-300 bg-white px-4 py-2.5 text-sm font-medium text-stone-700 transition hover:border-amber-400 hover:text-amber-700">从媒体库选择</button>
                                <input id="cover_upload" name="cover_upload" type="file" accept="image/*" class="hidden" style="display:none" tabindex="-1" data-cover-upload-input>

                                <div class="fixed inset-0 z-50 hidden p-4 sm:p-6" data-cover-library-panel>
                                    <div class="absolute inset-0 bg-stone-950/20" data-close-cover-library></div>
                                    <div class="relative mx-auto mt-16 w-full max-w-3xl rounded-[18px] border border-slate-200 bg-white p-4 shadow-[0_18px_55px_rgba(148,163,184,0.18),0_6px_20px_rgba(191,219,254,0.16)] sm:mt-20 sm:p-5" data-cover-library-dialog>
                                        <div class="mb-4 flex items-center justify-between gap-4">
                                            <label class="text-base font-medium text-stone-800">从媒体库选择封面</label>
                                            <button type="button" class="inline-flex items-center justify-center border border-stone-200 px-3 py-1.5 text-sm text-stone-500 transition hover:border-stone-300 hover:text-stone-700" data-close-cover-library>关闭</button>
                                        </div>
                                        <label class="flex cursor-pointer items-center gap-3 border border-stone-200 bg-stone-50 px-4 py-3 text-sm text-stone-700">
                                            <input type="radio" name="cover_attachment_id" value="" @checked(blank(old('cover_attachment_id', $post->cover_attachment_id))) class="text-amber-500 focus:ring-amber-400">
                                            <span>不使用媒体库图片</span>
                                        </label>

                                        @if ($coverAttachments->isNotEmpty())
                                            <div class="mt-4 grid max-h-[62vh] grid-cols-3 gap-2 overflow-y-auto pr-1 sm:grid-cols-4 lg:grid-cols-5">
                                                @foreach ($coverAttachments as $attachment)
                                                    <label class="group block cursor-pointer overflow-hidden rounded-[10px] border border-stone-200 bg-white shadow-[0_6px_18px_rgba(148,163,184,0.10)] transition hover:border-amber-300" title="{{ $attachment->filename }} · {{ $attachment->readable_size }}">
                                                        <input type="radio" name="cover_attachment_id" value="{{ $attachment->id }}" @checked((string) old('cover_attachment_id', $post->cover_attachment_id) === (string) $attachment->id) class="sr-only peer" data-cover-option data-cover-url="{{ $attachment->url }}" data-cover-label="当前将使用你在媒体库中选择的图片作为封面。" data-cover-meta="{{ $attachment->filename }} · {{ $attachment->readable_size }}">
                                                        <div class="aspect-[4/3] overflow-hidden bg-stone-100">
                                                            <div class="relative h-full w-full">
                                                                <img src="{{ $attachment->url }}" alt="{{ $attachment->filename }}" class="h-full w-full object-cover transition duration-200 peer-checked:scale-[1.02] group-hover:scale-[1.02]">
                                                                <div class="pointer-events-none absolute inset-x-0 bottom-0 bg-gradient-to-t from-stone-950/65 via-stone-950/10 to-transparent px-3 py-2 text-[11px] font-medium text-white opacity-0 transition duration-200 group-hover:opacity-100 peer-checked:opacity-100">
                                                                    {{ $attachment->filename }} · {{ $attachment->readable_size }}
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </label>
                                                @endforeach
                                            </div>
                                        @else
                                            <p class="mt-3 rounded-2xl border border-dashed border-stone-300 px-4 py-4 text-sm leading-6 text-stone-500">你还没有上传过封面图片。先在上方直接上传一次，后续就可以在这里重复选择。</p>
                                        @endif

                                        @error('cover_attachment_id')
                                            <p class="mt-3 text-xs text-rose-600">{{ $message }}</p>
                                        @enderror
                                    </div>
                                </div>
                            </div>
                        </div>

                    </div>
                </section>

                <aside class="space-y-6">
                    <div class="rounded-[28px] border border-stone-200/70 bg-white/90 p-6 shadow-sm">
                        <p class="text-sm font-semibold text-stone-900">发布设置</p>
                        <div class="mt-4 space-y-4">
                            <div>
                                <label for="category_id" class="text-sm font-medium text-stone-700">栏目</label>
                                <select id="category_id" name="category_id" required class="mt-2 w-full rounded-2xl border border-stone-200 bg-stone-50 px-4 py-3 text-sm text-stone-900 outline-none transition focus:border-amber-400 focus:bg-white">
                                    @foreach ($categories as $category)
                                        <option value="{{ $category->id }}" @selected((string) old('category_id', $post->category_id) === (string) $category->id)>{{ $category->name }} · {{ $category->contentModel?->name ?? '未绑定模型' }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <div>
                                <label for="seo_title" class="text-sm font-medium text-stone-700">SEO 标题</label>
                                <input id="seo_title" name="seo_title" type="text" value="{{ old('seo_title', $post->seo_title) }}" class="mt-2 w-full rounded-2xl border border-stone-200 bg-stone-50 px-4 py-3 text-sm text-stone-900 outline-none transition focus:border-amber-400 focus:bg-white">
                            </div>

                            <div>
                                <label for="tags" class="text-sm font-medium text-stone-700">标签</label>
                                <input id="tags" name="tags" type="text" value="{{ old('tags', $post->tags->pluck('name')->implode(', ')) }}" class="mt-2 w-full rounded-2xl border border-stone-200 bg-stone-50 px-4 py-3 text-sm text-stone-900 outline-none transition focus:border-amber-400 focus:bg-white">
                            </div>

                            <div>
                                <label for="status" class="text-sm font-medium text-stone-700">稿件状态</label>
                                <select id="status" name="status" required class="mt-2 w-full rounded-2xl border border-stone-200 bg-stone-50 px-4 py-3 text-sm text-stone-900 outline-none transition focus:border-amber-400 focus:bg-white">
                                    <option value="draft" @selected(old('status', $post->status) === 'draft')>保存草稿</option>
                                    <option value="pending" @selected(old('status', $post->status) === 'pending')>提交审核</option>
                                </select>
                            </div>
                        </div>
                    </div>

                    <div class="rounded-[28px] border border-stone-200 bg-[linear-gradient(135deg,rgba(255,251,235,.78),rgba(255,255,255,.98))] p-6 shadow-sm">
                        <button type="submit" class="inline-flex w-full items-center justify-center rounded-full border border-amber-200 bg-amber-100 px-4 py-3 text-sm font-semibold text-amber-950 transition hover:bg-amber-200">确认投稿</button>
                    </div>
                </aside>
            </form>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            document.querySelectorAll('[data-cover-picker]').forEach(function (picker) {
                var uploadInput = picker.querySelector('[data-cover-upload-input]');
                var toggleButton = picker.querySelector('[data-toggle-cover-library]');
                var libraryPanel = picker.querySelector('[data-cover-library-panel]');
                var libraryDialog = picker.querySelector('[data-cover-library-dialog]');
                var previewRoot = picker.querySelector('[data-cover-preview]');
                var previewFilled = previewRoot.querySelector('[data-cover-preview-filled]');
                var previewEmpty = previewRoot.querySelector('[data-cover-preview-empty]');
                var previewImage = previewRoot.querySelector('[data-cover-preview-image]');
                var previewLabel = previewRoot.querySelector('[data-cover-preview-label]');
                var previewMeta = previewRoot.querySelector('[data-cover-preview-meta]');

                toggleButton?.addEventListener('click', function () {
                    libraryPanel?.classList.toggle('hidden');
                });

                libraryPanel?.querySelectorAll('[data-close-cover-library]').forEach(function (closeButton) {
                    closeButton.addEventListener('click', function () {
                        libraryPanel.classList.add('hidden');
                    });
                });

                libraryDialog?.addEventListener('click', function (event) {
                    event.stopPropagation();
                });

                document.addEventListener('click', function (event) {
                    if (!picker.contains(event.target)) {
                        libraryPanel?.classList.add('hidden');
                    }
                });

                picker.querySelectorAll('input[name="cover_attachment_id"]').forEach(function (radio) {
                    radio.addEventListener('change', function () {
                        if (radio.dataset.coverUrl) {
                            if (!previewFilled) {
                                previewFilled = document.createElement('div');
                                previewFilled.className = 'mt-3 overflow-hidden rounded-[20px] border border-stone-200 bg-white';
                                previewFilled.setAttribute('data-cover-preview-filled', '');
                                previewFilled.innerHTML = '<div class="aspect-[16/9] bg-stone-100"><img alt="封面预览" class="h-full w-full object-cover" data-cover-preview-image></div><div class="space-y-1 border-t border-stone-100 px-4 py-3"><p class="text-sm text-stone-700" data-cover-preview-label></p><p class="break-all text-xs text-stone-500" data-cover-preview-meta></p></div>';
                                previewRoot.appendChild(previewFilled);
                                previewImage = previewFilled.querySelector('[data-cover-preview-image]');
                                previewLabel = previewFilled.querySelector('[data-cover-preview-label]');
                                previewMeta = previewFilled.querySelector('[data-cover-preview-meta]');
                            }

                            if (previewEmpty) {
                                previewEmpty.classList.add('hidden');
                            }

                            previewFilled.classList.remove('hidden');
                            previewImage.src = radio.dataset.coverUrl;
                            previewLabel.textContent = radio.dataset.coverLabel || '当前将使用你在媒体库中选择的图片作为封面。';
                            previewMeta.textContent = radio.dataset.coverMeta || '';
                        }

                        libraryPanel?.classList.add('hidden');
                    });
                });

                uploadInput?.addEventListener('change', function (event) {
                    var file = event.target.files && event.target.files[0];

                    if (!file) {
                        return;
                    }

                    var reader = new FileReader();

                    reader.onload = function (loadEvent) {
                        if (!previewFilled) {
                            previewFilled = document.createElement('div');
                            previewFilled.className = 'mt-3 overflow-hidden rounded-[20px] border border-stone-200 bg-white';
                            previewFilled.setAttribute('data-cover-preview-filled', '');
                            previewFilled.innerHTML = '<div class="aspect-[16/9] bg-stone-100"><img alt="封面预览" class="h-full w-full object-cover" data-cover-preview-image></div><div class="space-y-1 border-t border-stone-100 px-4 py-3"><p class="text-sm text-stone-700" data-cover-preview-label></p><p class="break-all text-xs text-stone-500" data-cover-preview-meta></p></div>';
                            previewRoot.appendChild(previewFilled);
                            previewImage = previewFilled.querySelector('[data-cover-preview-image]');
                            previewLabel = previewFilled.querySelector('[data-cover-preview-label]');
                            previewMeta = previewFilled.querySelector('[data-cover-preview-meta]');
                        }

                        if (previewEmpty) {
                            previewEmpty.classList.add('hidden');
                        }

                        previewFilled.classList.remove('hidden');
                        previewImage.src = loadEvent.target.result;
                        previewLabel.textContent = '当前将使用你刚选择的本地图片作为封面。';
                        previewMeta.textContent = file.name + ' · ' + Math.round(file.size / 1024) + ' KB';
                    };

                    reader.readAsDataURL(file);
                });
            });
        });
    </script>
@endsection
