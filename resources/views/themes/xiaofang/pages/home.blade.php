@extends(\App\Support\SiteTheme::view('layout', 'themes.xiaofang.layout'), ['title' => ($siteSettings->seo_title ?? $siteSettings->site_name ?? '小芳侠') . ' - 首页'])

@section('content')
    <div class="space-y-8 bg-white pb-0">
        <section class="space-y-10 bg-white">
            <div class="grid gap-8 border-b border-[#d8d1c8] pb-10 lg:grid-cols-[minmax(0,1.15fr)_minmax(0,0.82fr)_320px]">
                <div class="flex flex-col">
                    @if ($heroLeadPost)
                        <article class="flex flex-col">
                            @if ($heroLeadPost->cover_image_url)
                                <a href="{{ route('posts.show', $heroLeadPost->slug) }}" class="block aspect-[16/10] overflow-hidden bg-[#ece8e1]">
                                    <img src="{{ $heroLeadPost->cover_image_url }}" alt="{{ $heroLeadPost->title }}" class="block h-full w-full object-cover">
                                </a>
                            @endif
                            <div class="space-y-4 pt-4">
                                <div class="text-[11px] font-semibold uppercase tracking-[0.24em] text-[#8b8175]">
                                    @if ($heroLeadPost->category)
                                        <a href="{{ route('categories.show', $heroLeadPost->category->slug) }}" class="transition hover:text-[#151515]">{{ $heroLeadPost->category->name }}</a>
                                    @else
                                        Feature
                                    @endif
                                </div>
                                <h1 class="max-w-3xl overflow-hidden font-serif text-4xl font-semibold leading-[1.04] text-[#151515] sm:text-5xl lg:text-[3.3rem]" style="display:-webkit-box;-webkit-box-orient:vertical;-webkit-line-clamp:2;min-height:2.08em;max-height:2.08em;">
                                    <a href="{{ route('posts.show', $heroLeadPost->slug) }}">{{ $heroLeadPost->title }}</a>
                                </h1>
                                <p class="max-w-3xl overflow-hidden text-base leading-7 text-[#585148]" style="display:-webkit-box;-webkit-box-orient:vertical;-webkit-line-clamp:3;min-height:5.25rem;max-height:5.25rem;">
                                    {{ $heroLeadPost->summary ?: ($siteSettings->hero_body ?? '把内容、人物、生活方式与活动编织成刊物式首页。') }}
                                </p>
                                <div class="flex flex-wrap items-center gap-x-4 gap-y-2 text-sm text-[#8b8175]">
                                    <span>By <a href="{{ route('authors.show', $heroLeadPost->user->username) }}" class="text-[#151515]">{{ $heroLeadPost->user->public_display_name }}</a></span>
                                    <span>{{ optional($heroLeadPost->published_at)->format('Y.m.d') }}</span>
                                    <span>{{ max(1, (int) ceil(str_word_count(strip_tags($heroLeadPost->content ?? $heroLeadPost->summary ?? '')) / 250)) }} min read</span>
                                </div>
                            </div>
                        </article>
                    @else
                        <div class="border border-dashed border-[#d8d1c8] bg-white px-6 py-10 text-sm text-[#6b6256]">当前还没有主稿内容。</div>
                    @endif

                    @if ($stripStory)
                        <article class="mt-6 border-t border-[#d8d1c8] pt-6 md:flex md:items-start md:gap-4">
                            @if ($stripStory->cover_image_url)
                                <a href="{{ route('posts.show', $stripStory->slug) }}" class="block h-[130px] w-full overflow-hidden bg-[#ece8e1] md:w-[180px] md:shrink-0">
                                    <img src="{{ $stripStory->cover_image_url }}" alt="{{ $stripStory->title }}" class="block h-full w-full object-cover">
                                </a>
                            @endif
                            <div class="min-w-0 md:flex-1">
                                <div class="text-[11px] font-semibold uppercase tracking-[0.24em] text-[#8b8175]">
                                    @if ($stripStory->category)
                                        <a href="{{ route('categories.show', $stripStory->category->slug) }}" class="transition hover:text-[#151515]">{{ $stripStory->category->name }}</a>
                                    @else
                                        Story
                                    @endif
                                </div>
                                <h2 class="mt-2 overflow-hidden font-serif text-[1.55rem] font-semibold leading-[1.18] text-[#151515]" style="display:-webkit-box;-webkit-box-orient:vertical;-webkit-line-clamp:2;min-height:2.36em;max-height:2.36em;">
                                    <a href="{{ route('posts.show', $stripStory->slug) }}">{{ $stripStory->title }}</a>
                                </h2>
                                <p class="mt-2 overflow-hidden text-[0.86rem] leading-5 text-[#6a6259]" style="display:-webkit-box;-webkit-box-orient:vertical;-webkit-line-clamp:2;min-height:2.5rem;max-height:2.5rem;">{{ $stripStory->summary ?: '继续阅读这条窄幅精选内容。' }}</p>
                                <div class="mt-3 text-xs text-[#8b8175]">By {{ $stripStory->user->public_display_name }} · {{ optional($stripStory->published_at)->format('m.d') }}</div>
                            </div>
                        </article>
                    @endif
                </div>

                <div class="grid gap-6 border-t border-[#d8d1c8] pt-6 lg:grid-rows-2 lg:border-l lg:border-t-0 lg:pl-6 lg:pt-0">
                    @forelse ($editorialStories->take(2) as $story)
                        <article class="grid gap-4 border-b border-[#e1dbd3] pb-6 last:border-b-0 last:pb-0 lg:grid-rows-[auto_1fr]">
                            @if ($story->cover_image_url)
                                <a href="{{ route('posts.show', $story->slug) }}" class="block aspect-video overflow-hidden bg-[#ece8e1]">
                                    <img src="{{ $story->cover_image_url }}" alt="{{ $story->title }}" class="block h-full w-full object-cover">
                                </a>
                            @endif
                            <div>
                                <div class="text-[11px] font-semibold uppercase tracking-[0.24em] text-[#8b8175]">
                                    @if ($story->category)
                                        <a href="{{ route('categories.show', $story->category->slug) }}" class="transition hover:text-[#151515]">{{ $story->category->name }}</a>
                                    @else
                                        Story
                                    @endif
                                </div>
                                <h2 class="mt-3 overflow-hidden font-serif text-[1.9rem] font-semibold leading-[1.12] text-[#151515]" style="display:-webkit-box;-webkit-box-orient:vertical;-webkit-line-clamp:2;min-height:2.24em;max-height:2.24em;">
                                    <a href="{{ route('posts.show', $story->slug) }}">{{ $story->title }}</a>
                                </h2>
                                <p class="mt-3 overflow-hidden text-[0.9rem] leading-6 text-[#5f574f]" style="display:-webkit-box;-webkit-box-orient:vertical;-webkit-line-clamp:2;min-height:3rem;max-height:3rem;">{{ $story->summary ?: '继续阅读这篇刊物风故事。' }}</p>
                                <div class="mt-4 text-xs text-[#8b8175]">By {{ $story->user->public_display_name }} · {{ optional($story->published_at)->format('m.d') }}</div>
                            </div>
                        </article>
                    @empty
                        <div class="border border-dashed border-[#d8d1c8] bg-white px-5 py-8 text-sm text-[#6b6256]">还没有精选故事。</div>
                    @endforelse
                </div>

                <section class="overflow-hidden border border-[#d8d1c8] bg-white px-5 py-6">
                    <div class="flex items-end justify-between gap-3">
                        <h2 class="font-serif text-2xl font-semibold text-[#151515]">Latest Articles</h2>
                        <a href="{{ route('search') }}" class="text-sm text-[#8b8175] transition hover:text-[#151515]">View all</a>
                    </div>
                    <div class="mt-5 space-y-4">
                        @forelse ($latestList as $story)
                            <article class="flex items-start gap-3 overflow-hidden border-b border-[#ece7e0] pb-4 last:border-b-0 last:pb-0">
                                <div class="min-w-0 flex-1">
                                    <h3 class="overflow-hidden font-serif text-[1.12rem] font-semibold leading-[1.28] text-[#151515]" style="display:-webkit-box;-webkit-box-orient:vertical;-webkit-line-clamp:2;min-height:2.56em;max-height:2.56em;padding-bottom:0.06em;">
                                        <a href="{{ route('posts.show', $story->slug) }}">{{ $story->title }}</a>
                                    </h3>
                                    <div class="mt-2 text-xs text-[#8b8175]">By {{ $story->user->public_display_name }} · {{ optional($story->published_at)->diffForHumans() }}</div>
                                </div>
                                @if ($story->cover_image_url)
                                    <a href="{{ route('posts.show', $story->slug) }}" aria-label="{{ $story->title }}" class="mt-1 block shrink-0 overflow-hidden bg-[#efebe5]" style="width:62px;height:62px;min-width:62px;max-width:62px;flex:0 0 62px;">
                                        <img src="{{ $story->cover_image_url }}" alt="{{ $story->title }}" width="62" height="62" class="block" style="width:62px;height:62px;min-width:62px;max-width:62px;object-fit:cover;display:block;">
                                    </a>
                                @else
                                    <div class="mt-1 shrink-0 bg-[#efebe5]" style="width:62px;height:62px;min-width:62px;max-width:62px;flex:0 0 62px;"></div>
                                @endif
                            </article>
                        @empty
                            <div class="text-sm text-[#6b6256]">最新文章稍后更新。</div>
                        @endforelse
                    </div>
                </section>
            </div>

            @if ($groupStories->isNotEmpty())
                <section class="grid gap-8 border-b border-[#d8d1c8] pb-10 lg:grid-cols-[minmax(0,1.12fr)_320px] lg:items-start">
                    <div>
                        <div class="mb-6">
                            <p class="text-[11px] font-semibold uppercase tracking-[0.28em] text-[#8b8175]">Editorial Group</p>
                            <h2 class="mt-2 font-serif text-4xl font-semibold text-[#151515]">{{ $groupLabel }}</h2>
                        </div>

                        <div class="grid gap-8 lg:grid-cols-[1.05fr_0.95fr] lg:items-start">
                            <div>
                                @if ($groupLead)
                                    <article class="space-y-4">
                                        @if ($groupLead->cover_image_url)
                                            <a href="{{ route('posts.show', $groupLead->slug) }}" class="block aspect-video overflow-hidden bg-[#ece8e1]">
                                                <img src="{{ $groupLead->cover_image_url }}" alt="{{ $groupLead->title }}" class="block h-full w-full object-cover">
                                            </a>
                                        @endif
                                        <div>
                                            <h3 class="font-serif text-3xl font-semibold leading-tight text-[#151515]">
                                                <a href="{{ route('posts.show', $groupLead->slug) }}">{{ $groupLead->title }}</a>
                                            </h3>
                                            <p class="mt-3 text-base leading-8 text-[#5f574f]">{{ $groupLead->summary ?: '进入该组的主稿内容。' }}</p>
                                            <div class="mt-4 text-sm text-[#8b8175]">By {{ $groupLead->user->public_display_name }} · {{ optional($groupLead->published_at)->format('Y.m.d') }}</div>
                                        </div>
                                    </article>
                                @endif
                            </div>

                            <div class="space-y-5">
                                @foreach ($groupItems as $story)
                                    <article class="flex items-start gap-4 overflow-hidden border-b border-[#e1dbd3] pb-5 last:border-b-0 last:pb-0">
                                        <div class="min-w-0 flex-1">
                                            <h3 class="font-serif text-2xl font-semibold leading-tight text-[#151515]">
                                                <a href="{{ route('posts.show', $story->slug) }}">{{ $story->title }}</a>
                                            </h3>
                                            <p class="mt-2 text-sm leading-7 text-[#5f574f]">{{ \Illuminate\Support\Str::limit($story->summary ?: '继续阅读该组更多内容。', 88) }}</p>
                                            <div class="mt-3 text-xs text-[#8b8175]">By {{ $story->user->public_display_name }} · {{ optional($story->published_at)->format('m.d') }}</div>
                                        </div>
                                        @if ($story->cover_image_url)
                                            <a href="{{ route('posts.show', $story->slug) }}" aria-label="{{ $story->title }}" class="mt-1 block shrink-0 overflow-hidden bg-[#ece8e1]" style="width:112px;height:112px;min-width:112px;max-width:112px;flex:0 0 112px;">
                                                <img src="{{ $story->cover_image_url }}" alt="{{ $story->title }}" width="112" height="112" class="block" style="width:112px;height:112px;min-width:112px;max-width:112px;object-fit:cover;display:block;">
                                            </a>
                                        @else
                                            <div class="mt-1 shrink-0 bg-[#efebe5]" style="width:112px;height:112px;min-width:112px;max-width:112px;flex:0 0 112px;"></div>
                                        @endif
                                    </article>
                                @endforeach
                            </div>
                        </div>
                    </div>

                    <aside class="space-y-6">
                        <section class="border border-[#d8d1c8] bg-white px-5 py-6">
                            <div class="flex items-end justify-between gap-3">
                                <h2 class="font-serif text-2xl font-semibold text-[#151515]">{{ $homeContent['membership_title'] ?? 'Be the first to know' }}</h2>
                                <a href="{{ route('pricing') }}" class="text-sm text-[#8b8175] transition hover:text-[#151515]">View plans</a>
                            </div>
                            <p class="mt-3 text-sm leading-7 text-[#6b6256]">{{ $homeContent['membership_copy'] ?? '订阅刊物更新，接收每周文章、人物与最新活动摘要。' }}</p>
                            <div class="mt-5 space-y-3">
                                <input type="email" placeholder="Your email address" class="w-full border border-[#d8d1c8] bg-white px-4 py-3 text-sm text-[#151515] outline-none placeholder:text-[#9b9388]">
                                <button type="button" class="inline-flex w-full items-center justify-center bg-[#111111] px-4 py-3 text-sm font-semibold text-white transition hover:bg-[#232323]">
                                    Subscribe
                                </button>
                            </div>
                        </section>

                        <section class="border border-[#d8d1c8] bg-white px-5 py-6">
                            <div class="flex items-end justify-between gap-3">
                                <h2 class="font-serif text-2xl font-semibold text-[#151515]">Latest Events</h2>
                                <button type="button" class="text-sm text-[#8b8175] transition hover:text-[#151515]" data-xf-events-trigger>Open panel</button>
                            </div>
                            <div class="mt-5 space-y-4">
                                @forelse ($xiaofangLatestEvents->take(3) as $event)
                                    <a href="{{ $event['url'] }}" class="block border-b border-[#ece7e0] pb-4 last:border-b-0 last:pb-0">
                                        <div class="text-[11px] font-semibold uppercase tracking-[0.22em] text-[#8b8175]">
                                            {{ $event['status'] === 'registration-open' ? 'Open Registration' : ($event['status'] === 'sold-out' ? 'Sold Out' : 'Archive') }}
                                        </div>
                                        <div class="mt-2 font-serif text-xl font-semibold leading-8 text-[#151515]">{{ $event['title'] }}</div>
                                        <div class="mt-2 text-sm leading-6 text-[#6b6256]">{{ $event['location'] }} · {{ $event['starts_at']?->format('m.d H:i') ?? '待更新' }}</div>
                                    </a>
                                @empty
                                    <div class="text-sm text-[#6b6256]">最新活动稍后公布。</div>
                                @endforelse
                            </div>
                        </section>
                    </aside>
                </section>
            @endif

            @if ($designGroupStories->isNotEmpty())
                <section class="border-b border-[#d8d1c8] pb-10">
                    <div class="mb-6">
                        <p class="text-[11px] font-semibold uppercase tracking-[0.28em] text-[#8b8175]">Editorial Group</p>
                        <h2 class="mt-2 font-serif text-4xl font-semibold text-[#151515]">{{ $designGroupLabel }}</h2>
                    </div>

                    <div class="grid gap-8 lg:grid-cols-[1.05fr_0.95fr] lg:items-start">
                        <div>
                            @if ($designGroupLead)
                                <article class="space-y-4">
                                    @if ($designGroupLead->cover_image_url)
                                        <a href="{{ route('posts.show', $designGroupLead->slug) }}" class="block aspect-video overflow-hidden bg-[#ece8e1]">
                                            <img src="{{ $designGroupLead->cover_image_url }}" alt="{{ $designGroupLead->title }}" class="block h-full w-full object-cover">
                                        </a>
                                    @endif
                                    <div>
                                        <h3 class="font-serif text-3xl font-semibold leading-tight text-[#151515]">
                                            <a href="{{ route('posts.show', $designGroupLead->slug) }}">{{ $designGroupLead->title }}</a>
                                        </h3>
                                        <p class="mt-3 text-base leading-8 text-[#5f574f]">{{ $designGroupLead->summary ?: '进入该组的主稿内容。' }}</p>
                                        <div class="mt-4 text-sm text-[#8b8175]">By {{ $designGroupLead->user->public_display_name }} · {{ optional($designGroupLead->published_at)->format('Y.m.d') }}</div>
                                    </div>
                                </article>
                            @endif
                        </div>

                        <div class="space-y-5">
                            @foreach ($designGroupItems as $story)
                                <article class="flex items-start gap-4 overflow-hidden border-b border-[#e1dbd3] pb-5 last:border-b-0 last:pb-0">
                                    <div class="min-w-0 flex-1">
                                        <h3 class="font-serif text-2xl font-semibold leading-tight text-[#151515]">
                                            <a href="{{ route('posts.show', $story->slug) }}">{{ $story->title }}</a>
                                        </h3>
                                        <p class="mt-2 text-sm leading-7 text-[#5f574f]">{{ \Illuminate\Support\Str::limit($story->summary ?: '继续阅读该组更多内容。', 88) }}</p>
                                        <div class="mt-3 text-xs text-[#8b8175]">By {{ $story->user->public_display_name }} · {{ optional($story->published_at)->format('m.d') }}</div>
                                    </div>
                                    @if ($story->cover_image_url)
                                        <a href="{{ route('posts.show', $story->slug) }}" aria-label="{{ $story->title }}" class="mt-1 block shrink-0 overflow-hidden bg-[#ece8e1]" style="width:112px;height:112px;min-width:112px;max-width:112px;flex:0 0 112px;">
                                            <img src="{{ $story->cover_image_url }}" alt="{{ $story->title }}" width="112" height="112" class="block" style="width:112px;height:112px;min-width:112px;max-width:112px;object-fit:cover;display:block;">
                                        </a>
                                    @else
                                        <div class="mt-1 shrink-0 bg-[#efebe5]" style="width:112px;height:112px;min-width:112px;max-width:112px;flex:0 0 112px;"></div>
                                    @endif
                                </article>
                            @endforeach
                        </div>
                    </div>
                </section>
            @endif
        </section>

        <section
            class="text-white"
            style="background:
                radial-gradient(circle at top left, rgba(74, 163, 255, 0.24), transparent 30%),
                radial-gradient(circle at 78% 18%, rgba(112, 92, 255, 0.16), transparent 24%),
                linear-gradient(135deg, #06101f 0%, #0b2347 48%, #123b6d 100%);"
        >
            <div class="px-4 py-8 sm:px-6 lg:px-8">
                <div class="grid gap-8 lg:grid-cols-[0.48fr_1.52fr] lg:items-end">
                    <div>
                        <p class="text-[11px] font-semibold uppercase tracking-[0.28em] text-white/70">Inspiration</p>
                        <h2 class="mt-4 font-serif text-5xl font-semibold leading-tight">创作灵感与编辑节奏</h2>
                        <p class="mt-4 text-sm leading-7 text-white/82">这里不再保留“栏目精选”调用，整个区域统一展开成一整条灵感与人物内容带，直接服务首页叙事。</p>
                    </div>

                    <div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
                        @forelse ($inspirationStories as $story)
                            <article class="border border-white/30 bg-white px-4 py-4 text-[#151515] shadow-[0_14px_35px_rgba(17,17,17,0.08)]">
                                @if ($story->cover_image_url)
                                    <a href="{{ route('posts.show', $story->slug) }}" class="block aspect-[5/6] overflow-hidden bg-[#ece8e1]">
                                        <img src="{{ $story->cover_image_url }}" alt="{{ $story->title }}" class="block h-full w-full object-cover">
                                    </a>
                                @endif
                                <h3 class="mt-4 font-serif text-2xl font-semibold leading-8">
                                    <a href="{{ route('posts.show', $story->slug) }}">{{ $story->title }}</a>
                                </h3>
                                <div class="mt-3 text-xs text-[#8b8175]">By {{ $story->user->public_display_name }} · {{ optional($story->published_at)->format('m.d') }}</div>
                            </article>
                        @empty
                            <div class="border border-dashed border-white/40 bg-white/12 px-4 py-6 text-sm text-white/80 sm:col-span-2 xl:col-span-4">灵感区内容稍后更新。</div>
                        @endforelse
                    </div>
                </div>
            </div>
        </section>

        <section class="bg-white">
            <div class="px-4 py-7 sm:px-6 lg:px-8">
                <div class="space-y-6">
                    <div>
                        <p class="text-[11px] font-semibold uppercase tracking-[0.28em] text-[#8b8175]">Read More</p>
                        <h2 class="mt-2 font-serif text-4xl font-semibold text-[#151515]">继续阅读</h2>
                        <p class="mt-2 text-sm leading-7 text-[#6b6256]">探索更多文章、人物和创作主题。</p>
                    </div>

                    <div class="grid gap-6 md:grid-cols-2 xl:grid-cols-4">
                        @forelse ($readMoreStories as $story)
                            <article class="border border-[#d8d1c8] bg-white">
                                @if ($story->cover_image_url)
                                    <a href="{{ route('posts.show', $story->slug) }}" class="block aspect-[4/3] overflow-hidden bg-[#ece8e1]">
                                        <img src="{{ $story->cover_image_url }}" alt="{{ $story->title }}" class="block h-full w-full object-cover">
                                    </a>
                                @endif
                                <div class="px-5 py-5">
                                    <div class="text-[11px] font-semibold uppercase tracking-[0.24em] text-[#8b8175]">
                                        @if ($story->category)
                                            <a href="{{ route('categories.show', $story->category->slug) }}" class="transition hover:text-[#151515]">{{ $story->category->name }}</a>
                                        @else
                                            Feature
                                        @endif
                                    </div>
                                    <h3 class="mt-3 font-serif text-2xl font-semibold leading-8 text-[#151515]">
                                        <a href="{{ route('posts.show', $story->slug) }}">{{ $story->title }}</a>
                                    </h3>
                                    <p class="mt-3 text-sm leading-7 text-[#5f574f]">{{ $story->summary ?: '继续阅读这篇内容。' }}</p>
                                    <div class="mt-4 text-xs text-[#8b8175]">By {{ $story->user->public_display_name }} · {{ optional($story->published_at)->format('Y.m.d') }}</div>
                                </div>
                            </article>
                        @empty
                            <div class="border border-dashed border-[#d8d1c8] bg-white px-5 py-8 text-sm text-[#6b6256] md:col-span-2 xl:col-span-4">当前还没有更多文章。</div>
                        @endforelse
                    </div>
                </div>
            </div>
        </section>

        <section class="bg-[#e6e8eb]">
            <div class="px-4 pt-3 pb-4 sm:px-6 lg:px-8">
                <div class="space-y-4">
                    <div>
                        <p class="text-[11px] font-semibold uppercase tracking-[0.28em] text-[#8b8175]">Meet Our Authors</p>
                        <h2 class="mt-2 font-serif text-4xl font-semibold text-[#151515]">认识我们的作者</h2>
                    </div>

                    <div class="grid gap-5 md:grid-cols-2 xl:grid-cols-4">
                        @forelse ($xiaofangFeaturedAuthors as $author)
                            <article class="border border-[#d8d1c8] bg-white px-5 py-5">
                                <div class="flex items-start gap-4">
                                    @if ($author['avatar_url'])
                                        <img src="{{ $author['avatar_url'] }}" alt="{{ $author['display_name'] }}" class="h-[72px] w-[72px] rounded-full object-cover">
                                    @else
                                        <div class="flex h-[72px] w-[72px] items-center justify-center rounded-full bg-[#efebe5] font-serif text-2xl text-[#151515]">
                                            {{ \Illuminate\Support\Str::substr($author['display_name'], 0, 1) }}
                                        </div>
                                    @endif
                                    <div class="min-w-0">
                                        <h3 class="font-serif text-2xl font-semibold leading-8 text-[#151515]">
                                            <a href="{{ $author['url'] }}">{{ $author['display_name'] }}</a>
                                        </h3>
                                        <div class="text-sm text-[#8b8175]">{{ $author['headline'] }}</div>
                                    </div>
                                </div>
                                <p class="mt-4 text-sm leading-7 text-[#5f574f]">{{ $author['bio'] }}</p>
                                <div class="mt-4 flex items-center justify-between gap-3 text-sm">
                                    <span class="text-[#8b8175]">{{ $author['posts_count'] }} 篇文章</span>
                                    <a href="{{ $author['url'] }}" class="font-medium text-[#151515] transition hover:text-[#5f574f]">View all</a>
                                </div>
                            </article>
                        @empty
                            <div class="border border-dashed border-[#d8d1c8] bg-white px-5 py-8 text-sm text-[#6b6256] md:col-span-2 xl:col-span-4">作者资料正在补充中。</div>
                        @endforelse
                    </div>
                </div>
            </div>
        </section>
    </div>
@endsection
