<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $currentTitle }} - 开发文档</title>
    <style>
        :root {
            --docs-bg: #ffffff;
            --docs-panel: #ffffff;
            --docs-sidebar: #fcfcfc;
            --docs-border: #e5e7eb;
            --docs-text: #1f2937;
            --docs-muted: #6b7280;
            --docs-accent: #3eaf7c;
            --docs-accent-soft: #f0fdf4;
            --docs-code-bg: #f6f8fa;
        }
        * { box-sizing: border-box; }
        body {
            margin: 0;
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", "PingFang SC", "Noto Sans SC", "Helvetica Neue", sans-serif;
            background: var(--docs-bg);
            color: var(--docs-text);
        }
        .docs-topbar {
            position: sticky;
            top: 0;
            z-index: 30;
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 16px;
            height: 58px;
            padding: 0 28px;
            border-bottom: 1px solid var(--docs-border);
            background: rgba(255, 255, 255, 0.94);
            backdrop-filter: blur(10px);
        }
        .docs-topbar-brand {
            font-size: 1rem;
            font-weight: 700;
            color: #111827;
            text-decoration: none;
        }
        .docs-topbar-links {
            display: flex;
            align-items: center;
            gap: 18px;
            color: var(--docs-muted);
            font-size: .92rem;
        }
        .docs-topbar-search {
            display: flex;
            align-items: center;
            min-width: min(360px, 42vw);
            max-width: 440px;
            width: 100%;
        }
        .docs-topbar-search form {
            width: 100%;
        }
        .docs-topbar-search input {
            width: 100%;
            height: 38px;
            padding: 0 14px;
            border: 1px solid var(--docs-border);
            border-radius: 10px;
            background: #fff;
            color: #111827;
            font-size: .92rem;
            outline: none;
        }
        .docs-topbar-search input:focus {
            border-color: #93c5fd;
            box-shadow: 0 0 0 3px rgba(59, 130, 246, .08);
        }
        .docs-topbar-links a {
            color: inherit;
            text-decoration: none;
        }
        .docs-topbar-links a:hover {
            color: #111827;
        }
        .docs-shell {
            display: grid;
            grid-template-columns: 280px minmax(0, 1fr) 220px;
            min-height: calc(100vh - 58px);
        }
        .docs-sidebar {
            position: sticky;
            top: 58px;
            align-self: start;
            height: calc(100vh - 58px);
            overflow-y: auto;
            padding: 24px 18px 40px;
            border-right: 1px solid var(--docs-border);
            background: var(--docs-sidebar);
        }
        .docs-brand {
            margin: 0 0 18px;
            font-size: .88rem;
            font-weight: 700;
            letter-spacing: .04em;
            color: #111827;
        }
        .docs-section + .docs-section {
            margin-top: 22px;
        }
        .docs-section-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 8px;
            margin: 0 0 10px;
        }
        .docs-section-title {
            margin: 0;
            font-size: .74rem;
            font-weight: 700;
            letter-spacing: .02em;
            color: var(--docs-muted);
        }
        .docs-section-toggle {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 24px;
            height: 24px;
            padding: 0;
            border: 0;
            background: transparent;
            color: #94a3b8;
            cursor: pointer;
            transition: color .18s ease, transform .18s ease;
        }
        .docs-section-toggle:hover {
            color: #111827;
        }
        .docs-section.is-collapsed .docs-section-toggle {
            transform: rotate(-90deg);
        }
        .docs-section.is-collapsed .docs-nav {
            display: none;
        }
        .docs-nav {
            margin: 0;
            padding: 0;
            list-style: none;
        }
        .docs-nav a {
            display: block;
            padding: 7px 10px;
            border-left: 2px solid transparent;
            color: var(--docs-text);
            text-decoration: none;
            line-height: 1.55;
            font-size: .94rem;
        }
        .docs-nav a:hover {
            color: #111827;
            background: #f8fafc;
        }
        .docs-nav a.is-active {
            border-left-color: var(--docs-accent);
            background: var(--docs-accent-soft);
            color: #111827;
            font-weight: 600;
        }
        .docs-main {
            min-width: 0;
            padding: 36px 48px 84px;
        }
        .docs-toc {
            position: sticky;
            top: 58px;
            align-self: start;
            height: calc(100vh - 58px);
            overflow-y: auto;
            padding: 28px 20px 40px 12px;
            border-left: 1px solid var(--docs-border);
            background: #fff;
        }
        .docs-toc-title {
            margin: 0 0 12px;
            font-size: .74rem;
            font-weight: 700;
            letter-spacing: .08em;
            text-transform: uppercase;
            color: var(--docs-muted);
        }
        .docs-toc-nav {
            margin: 0;
            padding: 0;
            list-style: none;
        }
        .docs-toc-nav li + li {
            margin-top: 4px;
        }
        .docs-toc-nav a {
            display: block;
            padding: 6px 0 6px 12px;
            border-left: 2px solid transparent;
            color: #64748b;
            text-decoration: none;
            font-size: .9rem;
            line-height: 1.45;
        }
        .docs-toc-nav a:hover {
            color: #111827;
        }
        .docs-toc-nav a.is-active {
            border-left-color: var(--docs-accent);
            color: #111827;
            font-weight: 600;
        }
        .docs-toc-nav a.level-3 {
            padding-left: 22px;
            font-size: .85rem;
        }
        .docs-article {
            max-width: 860px;
            margin: 0 auto;
            padding: 0;
            background: var(--docs-panel);
        }
        .docs-current {
            margin: 0 0 20px;
            font-size: .78rem;
            font-weight: 700;
            letter-spacing: .08em;
            text-transform: uppercase;
            color: var(--docs-muted);
        }
        .docs-landing-hero {
            margin: 0 0 30px;
            padding: 30px 32px;
            border: 1px solid var(--docs-border);
            border-radius: 18px;
            background:
                radial-gradient(circle at top right, rgba(62, 175, 124, 0.08), transparent 26%),
                radial-gradient(circle at left center, rgba(59, 130, 246, 0.06), transparent 18%),
                linear-gradient(180deg, #ffffff 0%, #fcfdfc 100%);
        }
        .docs-landing-kicker {
            margin: 0 0 10px;
            font-size: .76rem;
            font-weight: 700;
            letter-spacing: .12em;
            text-transform: uppercase;
            color: var(--docs-muted);
        }
        .docs-landing-title {
            margin: 0;
            font-size: 2.5rem;
            font-weight: 800;
            line-height: 1.08;
            color: #111827;
        }
        .docs-landing-copy {
            max-width: 720px;
            margin: 16px 0 0;
            font-size: 1.05rem;
            line-height: 1.85;
            color: #475569;
        }
        .docs-landing-grid {
            display: grid;
            grid-template-columns: repeat(3, minmax(0, 1fr));
            gap: 14px;
            margin: 24px 0 34px;
        }
        .docs-landing-card {
            display: block;
            padding: 18px 18px 16px;
            border: 1px solid var(--docs-border);
            border-radius: 14px;
            background: #fff;
            color: inherit;
            text-decoration: none;
            transition: transform .18s ease, border-color .18s ease, box-shadow .18s ease;
        }
        .docs-landing-card:hover {
            transform: translateY(-1px);
            border-color: #cbd5e1;
            box-shadow: 0 12px 28px rgba(15, 23, 42, 0.06);
            text-decoration: none;
        }
        .docs-landing-card-title {
            margin: 0;
            font-size: 1rem;
            font-weight: 700;
            line-height: 1.5;
            color: #111827;
        }
        .docs-landing-card-path {
            margin: 10px 0 0;
            font-size: .84rem;
            color: #64748b;
        }
        .docs-search-results {
            display: grid;
            gap: 14px;
        }
        .docs-search-group + .docs-search-group {
            margin-top: 28px;
        }
        .docs-search-group-title {
            margin: 0 0 12px;
            font-size: .8rem;
            font-weight: 700;
            letter-spacing: .08em;
            text-transform: uppercase;
            color: #64748b;
        }
        .docs-search-summary {
            margin: 0 0 22px;
            color: #64748b;
            font-size: .96rem;
        }
        .docs-search-card {
            display: block;
            padding: 18px 18px 16px;
            border: 1px solid var(--docs-border);
            border-radius: 14px;
            background: #fff;
            color: inherit;
            text-decoration: none;
            transition: border-color .18s ease, box-shadow .18s ease;
        }
        .docs-search-card:hover {
            border-color: #cbd5e1;
            box-shadow: 0 10px 24px rgba(15, 23, 42, 0.05);
            text-decoration: none;
        }
        .docs-search-card-title {
            margin: 0;
            font-size: 1.08rem;
            font-weight: 700;
            line-height: 1.5;
            color: #111827;
        }
        .docs-search-card-snippet {
            margin: 10px 0 0;
            color: #475569;
            line-height: 1.8;
            font-size: .96rem;
        }
        .docs-search-card-path {
            margin: 10px 0 0;
            color: #94a3b8;
            font-size: .84rem;
        }
        .docs-search-card mark {
            padding: 0 .18em;
            border-radius: 4px;
            background: #fef3c7;
            color: #92400e;
        }
        .docs-article h1,
        .docs-article h2,
        .docs-article h3 {
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", "PingFang SC", "Noto Sans SC", sans-serif;
            line-height: 1.2;
            color: #111827;
        }
        .docs-article h2,
        .docs-article h3 {
            position: relative;
        }
        .docs-article h1 { font-size: 2.2rem; margin: 0 0 1rem; }
        .docs-article h2 {
            font-size: 1.55rem;
            margin: 2.5rem 0 .95rem;
            padding-bottom: .45rem;
            border-bottom: 1px solid #eef2f7;
        }
        .docs-article h3 { font-size: 1.16rem; margin: 1.7rem 0 .75rem; }
        .docs-article p,
        .docs-article li {
            font-size: 1rem;
            line-height: 1.8;
            color: #334155;
        }
        .docs-article ul,
        .docs-article ol {
            padding-left: 1.3rem;
        }
        .docs-article code {
            padding: .14rem .4rem;
            border-radius: 5px;
            background: var(--docs-code-bg);
            font-family: ui-monospace, SFMono-Regular, Menlo, Monaco, Consolas, monospace;
            font-size: .92em;
        }
        .docs-article pre {
            overflow-x: auto;
            padding: 16px 18px;
            border-radius: 10px;
            background: #0f172a;
            color: #f8fafc;
        }
        .docs-article pre code {
            padding: 0;
            background: transparent;
            color: inherit;
        }
        .docs-article a {
            color: #2563eb;
            text-decoration: none;
        }
        .docs-article a:hover {
            text-decoration: underline;
        }
        .docs-anchor {
            margin-left: .55rem;
            color: #cbd5e1 !important;
            font-size: .9em;
            opacity: 0;
            text-decoration: none !important;
            transition: opacity .18s ease, color .18s ease;
        }
        .docs-article h2:hover .docs-anchor,
        .docs-article h3:hover .docs-anchor,
        .docs-anchor:focus {
            opacity: 1;
        }
        .docs-anchor:hover {
            color: var(--docs-accent) !important;
        }
        .docs-article blockquote {
            margin: 1.4rem 0;
            padding: .3rem 0 .3rem 1rem;
            border-left: 3px solid #cbd5e1;
            color: #64748b;
            background: #f8fafc;
        }
        .docs-code-block {
            position: relative;
        }
        .docs-copy-button {
            position: absolute;
            top: 10px;
            right: 10px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 6px;
            min-width: 68px;
            height: 30px;
            padding: 0 10px;
            border: 1px solid rgba(255, 255, 255, 0.12);
            border-radius: 8px;
            background: rgba(15, 23, 42, 0.88);
            color: #e2e8f0;
            font-size: .78rem;
            cursor: pointer;
            transition: background .18s ease, color .18s ease, border-color .18s ease;
        }
        .docs-copy-button:hover {
            background: rgba(30, 41, 59, 0.96);
            border-color: rgba(255, 255, 255, 0.2);
        }
        .docs-copy-button.is-copied {
            color: #bbf7d0;
            border-color: rgba(134, 239, 172, 0.35);
        }
        @media (max-width: 960px) {
            .docs-topbar {
                padding: 0 16px;
                height: auto;
                min-height: 58px;
                flex-wrap: wrap;
                padding-top: 10px;
                padding-bottom: 10px;
            }
            .docs-topbar-search {
                order: 3;
                min-width: 100%;
                max-width: 100%;
            }
            .docs-shell {
                grid-template-columns: 1fr;
            }
            .docs-sidebar {
                position: static;
                height: auto;
                border-right: 0;
                border-bottom: 1px solid var(--docs-border);
            }
            .docs-main {
                padding: 24px 18px 56px;
            }
            .docs-toc {
                display: none;
            }
            .docs-article {
                padding: 0;
            }
            .docs-landing-hero {
                padding: 24px 20px;
            }
            .docs-landing-title {
                font-size: 2rem;
            }
            .docs-landing-grid {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>
<body>
    <header class="docs-topbar">
        <a href="{{ route('developer.docs') }}" class="docs-topbar-brand">开发者文档中心</a>
        <div class="docs-topbar-search">
            <form action="{{ route('developer.docs') }}" method="get">
                <input type="search" name="q" value="{{ $searchQuery }}" placeholder="搜索文档标题、关键函数、控制器、模型..." autocomplete="off">
            </form>
        </div>
        <nav class="docs-topbar-links">
            <a href="{{ route('site.home') }}">前台首页</a>
            <a href="{{ route('developer.docs') }}">全部文档</a>
        </nav>
    </header>
    <div class="docs-shell">
        <aside class="docs-sidebar">
            <div class="docs-brand">Guide</div>
            @foreach ($sections as $section)
                <section class="docs-section" data-docs-section>
                    <div class="docs-section-header">
                        <h2 class="docs-section-title">{{ $section['section'] }}</h2>
                        <button type="button" class="docs-section-toggle" aria-expanded="true" aria-label="切换 {{ $section['section'] }} 目录">
                            <svg width="14" height="14" viewBox="0 0 20 20" fill="none" aria-hidden="true">
                                <path d="M5 8l5 5 5-5" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/>
                            </svg>
                        </button>
                    </div>
                    <ul class="docs-nav">
                        @foreach ($section['items'] as $item)
                            <li>
                                <a href="{{ route('developer.docs', ['page' => preg_replace('/\.md$/', '', $item['path'])]) }}" class="{{ $currentPath === $item['path'] ? 'is-active' : '' }}">
                                    {{ $item['title'] }}
                                </a>
                            </li>
                        @endforeach
                    </ul>
                </section>
            @endforeach
        </aside>
        <main class="docs-main">
            <article class="docs-article">
                <p class="docs-current">{{ $currentTitle }}</p>
                @if ($isLanding)
                    <section class="docs-landing-hero">
                        <p class="docs-landing-kicker">Developer Docs</p>
                        <h1 class="docs-landing-title">FFMeet 开发者文档中心</h1>
                        <p class="docs-landing-copy">
                            这里集中整理系统发布、架构、主题、首页、支付、安全、二次开发和运维说明。
                            文档中的项目路径已统一使用 <code>&lt;PROJECT_ROOT&gt;</code> 占位，表示你当前安装此系统的项目根目录。
                        </p>
                    </section>

                    <section class="docs-landing-grid">
                        @foreach ($landingCards as $card)
                            <a href="{{ route('developer.docs', ['page' => preg_replace('/\.md$/', '', $card['path'])]) }}" class="docs-landing-card">
                                <h2 class="docs-landing-card-title">{{ $card['title'] }}</h2>
                                <p class="docs-landing-card-path">{{ $card['path'] }}</p>
                            </a>
                        @endforeach
                    </section>
                    {!! $contentHtml !!}
                @elseif ($searchQuery !== '')
                    <p class="docs-search-summary">
                        @if ($searchResultCount > 0)
                            找到 {{ $searchResultCount }} 条与 <code>{{ $searchQuery }}</code> 相关的文档结果。
                        @else
                            没有找到与 <code>{{ $searchQuery }}</code> 相关的文档结果。
                        @endif
                    </p>
                    <section class="docs-search-results">
                        @foreach ($searchResults as $group)
                            <section class="docs-search-group">
                                <h2 class="docs-search-group-title">{{ $group['section'] }}</h2>
                                <div class="docs-search-results">
                                    @foreach ($group['items'] as $result)
                                        <a href="{{ route('developer.docs', ['page' => preg_replace('/\.md$/', '', $result['path'])]) }}" class="docs-search-card">
                                            <h3 class="docs-search-card-title">{!! $result['title_html'] !!}</h3>
                                            <p class="docs-search-card-snippet">{!! $result['snippet_html'] !!}</p>
                                            <p class="docs-search-card-path">{{ $result['path'] }}</p>
                                        </a>
                                    @endforeach
                                </div>
                            </section>
                        @endforeach
                    </section>
                @else
                    <p style="margin: 0 0 1.35rem; font-size: .92rem; color: #6b7280;">
                        文档中的项目路径已统一使用 <code>&lt;PROJECT_ROOT&gt;</code> 占位，表示你当前安装此系统的项目根目录。
                    </p>
                    {!! $contentHtml !!}
                @endif
            </article>
        </main>
        @if (! $isLanding && $searchQuery === '')
            <aside class="docs-toc" aria-label="当前页目录">
                <h2 class="docs-toc-title">On This Page</h2>
                <ul class="docs-toc-nav" id="docs-toc-list"></ul>
            </aside>
        @endif
    </div>
    <script>
        (() => {
            const article = document.querySelector('.docs-article');
            const toc = document.getElementById('docs-toc-list');

            if (!article || !toc) return;

            const headings = Array.from(article.querySelectorAll('h2, h3'));
            if (!headings.length) {
                const tocAside = document.querySelector('.docs-toc');
                if (tocAside) tocAside.style.display = 'none';
            }

            const slugify = (text) => {
                const ascii = text
                    .toLowerCase()
                    .trim()
                    .replace(/[^\w\u4e00-\u9fa5\s-]/g, '')
                    .replace(/\s+/g, '-');
                return ascii || 'section';
            };

            const used = new Map();
            headings.forEach((heading) => {
                const base = slugify(heading.textContent || '');
                const count = used.get(base) || 0;
                used.set(base, count + 1);
                heading.id = count ? `${base}-${count + 1}` : base;

                const anchor = document.createElement('a');
                anchor.href = `#${heading.id}`;
                anchor.className = 'docs-anchor';
                anchor.setAttribute('aria-label', `复制 ${heading.textContent || ''} 的锚点链接`);
                anchor.textContent = '#';
                heading.appendChild(anchor);

                const li = document.createElement('li');
                const a = document.createElement('a');
                a.href = `#${heading.id}`;
                a.textContent = heading.textContent || '';
                a.className = heading.tagName.toLowerCase() === 'h3' ? 'level-3' : 'level-2';
                li.appendChild(a);
                toc.appendChild(li);
            });

            const links = Array.from(toc.querySelectorAll('a'));
            const byId = new Map(links.map((link) => [link.getAttribute('href')?.slice(1), link]));

            const setActive = () => {
                let current = headings[0];
                const offset = 100;
                for (const heading of headings) {
                    if (heading.getBoundingClientRect().top <= offset) {
                        current = heading;
                    } else {
                        break;
                    }
                }

                links.forEach((link) => link.classList.remove('is-active'));
                const active = byId.get(current.id);
                if (active) active.classList.add('is-active');
            };

            setActive();
            document.addEventListener('scroll', setActive, { passive: true });

            article.querySelectorAll('pre').forEach((pre) => {
                const wrapper = document.createElement('div');
                wrapper.className = 'docs-code-block';
                pre.parentNode?.insertBefore(wrapper, pre);
                wrapper.appendChild(pre);

                const button = document.createElement('button');
                button.type = 'button';
                button.className = 'docs-copy-button';
                button.textContent = '复制代码';
                button.addEventListener('click', async () => {
                    const text = pre.innerText;
                    try {
                        await navigator.clipboard.writeText(text);
                        button.textContent = '已复制';
                        button.classList.add('is-copied');
                        window.setTimeout(() => {
                            button.textContent = '复制代码';
                            button.classList.remove('is-copied');
                        }, 1600);
                    } catch (_) {
                        button.textContent = '复制失败';
                        window.setTimeout(() => {
                            button.textContent = '复制代码';
                        }, 1600);
                    }
                });
                wrapper.appendChild(button);
            });

            document.querySelectorAll('[data-docs-section]').forEach((section, index) => {
                const key = `docs-section-${index}`;
                const button = section.querySelector('.docs-section-toggle');
                const stored = window.localStorage.getItem(key);
                const setCollapsed = (collapsed) => {
                    section.classList.toggle('is-collapsed', collapsed);
                    if (button) button.setAttribute('aria-expanded', String(!collapsed));
                    window.localStorage.setItem(key, collapsed ? '0' : '1');
                };

                if (stored === '0') {
                    setCollapsed(true);
                }

                button?.addEventListener('click', () => {
                    setCollapsed(!section.classList.contains('is-collapsed'));
                });
            });
        })();
    </script>
</body>
</html>
