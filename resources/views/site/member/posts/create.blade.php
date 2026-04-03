@extends('site.layout', ['title' => '会员投稿'])

@section('content')
    @include('site.member.partials.topbar')

    <div class="grid gap-6 lg:grid-cols-[320px_minmax(0,1fr)]">
        @include('site.member.partials.nav')

        <div class="space-y-6">
            <section class="rounded-[28px] border border-stone-200/70 bg-white/85 p-6 shadow-sm backdrop-blur">
                <div class="flex flex-col gap-3 md:flex-row md:items-end md:justify-between">
                    <div>
                        <p class="text-xs font-semibold uppercase tracking-[0.3em] text-amber-600">Member Submit</p>
                        <h1 class="mt-2 text-3xl font-semibold tracking-tight text-stone-900">发布新稿件</h1>
                        <p class="mt-2 max-w-2xl text-sm leading-6 text-stone-600">先提供一个清爽的前台投稿入口，会员可以保存草稿或直接送审，后面再接更完整的编辑器和媒体插图流程。</p>
                    </div>
                    <a href="{{ route('member.dashboard') }}" class="inline-flex items-center justify-center rounded-full border border-stone-200 px-4 py-2 text-sm font-medium text-stone-700 transition hover:border-amber-400 hover:text-amber-700">返回会员中心</a>
                </div>
            </section>

            <form method="POST" action="{{ route('member.posts.store') }}" class="grid gap-6 xl:grid-cols-[minmax(0,1fr)_320px]">
                @csrf

                <section class="space-y-6">
                    <div class="rounded-[28px] border border-stone-200/70 bg-white/90 p-6 shadow-sm">
                        <label for="title" class="text-sm font-medium text-stone-700">标题</label>
                        <input id="title" name="title" type="text" value="{{ old('title') }}" required class="mt-3 w-full rounded-2xl border border-stone-200 bg-stone-50 px-4 py-3 text-base text-stone-900 outline-none transition focus:border-amber-400 focus:bg-white">

                        <label for="summary" class="mt-5 block text-sm font-medium text-stone-700">摘要</label>
                        <textarea id="summary" name="summary" rows="4" class="mt-3 w-full rounded-2xl border border-stone-200 bg-stone-50 px-4 py-3 text-sm text-stone-900 outline-none transition focus:border-amber-400 focus:bg-white">{{ old('summary') }}</textarea>

                        <label for="content" class="mt-5 block text-sm font-medium text-stone-700">正文</label>
                        <textarea id="content" name="content" rows="18" required class="mt-3 w-full rounded-[24px] border border-stone-200 bg-stone-50 px-4 py-4 text-sm leading-7 text-stone-900 outline-none transition focus:border-amber-400 focus:bg-white">{{ old('content') }}</textarea>

                        <div class="mt-5 grid gap-5 md:grid-cols-2">
                            <div>
                                <label for="seo_title" class="text-sm font-medium text-stone-700">SEO 标题</label>
                                <input id="seo_title" name="seo_title" type="text" value="{{ old('seo_title') }}" class="mt-3 w-full rounded-2xl border border-stone-200 bg-stone-50 px-4 py-3 text-sm text-stone-900 outline-none transition focus:border-amber-400 focus:bg-white">
                            </div>
                            <div>
                                <label for="tags" class="text-sm font-medium text-stone-700">标签</label>
                                <input id="tags" name="tags" type="text" value="{{ old('tags') }}" placeholder="例如：CMS, Laravel, 新闻" class="mt-3 w-full rounded-2xl border border-stone-200 bg-stone-50 px-4 py-3 text-sm text-stone-900 outline-none transition focus:border-amber-400 focus:bg-white">
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
                                        <option value="{{ $category->id }}" @selected((string) old('category_id') === (string) $category->id)>{{ $category->name }} · {{ $category->contentModel?->name ?? '未绑定模型' }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="rounded-2xl border border-stone-200 bg-stone-50 px-4 py-3 text-sm text-stone-500">
                                文章模型由所选栏目自动决定，不再单独选择。
                            </div>

                            <div class="rounded-2xl border border-amber-200 bg-amber-50/80 px-4 py-3 text-sm leading-6 text-amber-900">
                                选择“快讯”栏目时，摘要会作为前台主展示内容，正文可留空；普通文章仍建议填写完整正文。
                            </div>

                            <div>
                                <label for="status" class="text-sm font-medium text-stone-700">投稿动作</label>
                                <select id="status" name="status" required class="mt-2 w-full rounded-2xl border border-stone-200 bg-stone-50 px-4 py-3 text-sm text-stone-900 outline-none transition focus:border-amber-400 focus:bg-white">
                                    <option value="draft" @selected(old('status', 'pending') === 'draft')>保存草稿</option>
                                    <option value="pending" @selected(old('status', 'pending') === 'pending')>提交审核</option>
                                </select>
                            </div>
                        </div>
                    </div>

                    <div class="rounded-[28px] border border-stone-200/70 bg-gradient-to-br from-stone-900 to-stone-800 p-6 text-stone-100 shadow-sm">
                        <p class="text-xs font-semibold uppercase tracking-[0.28em] text-amber-300">Author</p>
                        <p class="mt-3 text-xl font-semibold">{{ $user->username }}</p>
                        <p class="mt-2 text-sm leading-6 text-stone-300">当前前台投稿默认进入草稿或待审核状态，不直接发布，方便后面继续接权限和审核流。</p>
                        <button type="submit" class="mt-5 inline-flex w-full items-center justify-center rounded-full bg-amber-500 px-4 py-3 text-sm font-semibold text-stone-950 transition hover:bg-amber-400">保存稿件</button>
                    </div>
                </aside>
            </form>
        </div>
    </div>
@endsection
