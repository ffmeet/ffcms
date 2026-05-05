<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use Illuminate\View\View;

class DeveloperDocsController extends Controller
{
    protected const PROJECT_ROOT_PLACEHOLDER = '<PROJECT_ROOT>';

    protected const NAV = [
        [
            'section' => '发布',
            'items' => [
                ['title' => '1.0 发布清单', 'path' => 'release/1.0-release-plan.md'],
                ['title' => '1.0 审计与安全复核', 'path' => 'release/1.0-readiness-audit.md'],
                ['title' => '对外源码包与安装说明', 'path' => 'release/source-package-and-install.md'],
                ['title' => '公开仓库前检查清单', 'path' => 'release/public-repository-checklist.md'],
                ['title' => 'Git 初始化与首个提交说明', 'path' => 'release/git-initialization-and-first-commit.md'],
                ['title' => 'GitHub 仓库命名与首次发布说明', 'path' => 'release/github-repository-launch-guide.md'],
            ],
        ],
        [
            'section' => '架构',
            'items' => [
                ['title' => '系统总览', 'path' => 'architecture/overview.md'],
                ['title' => '主题与首页位置系统', 'path' => 'architecture/themes-and-homepage-slots.md'],
                ['title' => '会员、订单与支付链路', 'path' => 'architecture/membership-and-commerce.md'],
            ],
        ],
        [
            'section' => '开发',
            'items' => [
                ['title' => '二次开发指南', 'path' => 'development/secondary-development.md'],
                ['title' => '安装与部署说明', 'path' => 'development/installation-and-deployment.md'],
                ['title' => '主题模板函数调用规范', 'path' => 'development/theme-template-call-conventions.md'],
                ['title' => '控制器 / Builder / Blade 三层分工示例', 'path' => 'development/controller-builder-blade-layering.md'],
                ['title' => '首页内容调用开发说明', 'path' => 'development/homepage-content-calls.md'],
                ['title' => '文章与活动调用说明', 'path' => 'development/content-and-event-calls.md'],
                ['title' => '会员与支付调用说明', 'path' => 'development/member-commerce-calls.md'],
                ['title' => 'Filament 插件与版本清单', 'path' => 'development/filament-plugin-inventory.md'],
                ['title' => '路由与权限矩阵', 'path' => 'development/routes-and-permissions.md'],
            ],
        ],
        [
            'section' => '运维',
            'items' => [
                ['title' => '缓存与部署', 'path' => 'operations/cache-and-deployment.md'],
            ],
        ],
        [
            'section' => '安全',
            'items' => [
                ['title' => '安全基线', 'path' => 'security/security-baseline.md'],
                ['title' => '支付渠道接入与验签矩阵', 'path' => 'security/payment-provider-matrix.md'],
            ],
        ],
        [
            'section' => '专题说明',
            'items' => [
                ['title' => '首页数据来源', 'path' => 'HOMEPAGE_DATA_SOURCE.md'],
                ['title' => '会员资料显示规则', 'path' => 'MEMBER_PROFILE_DISPLAY_RULES.md'],
            ],
        ],
    ];

    public function __invoke(Request $request, ?string $page = null): View
    {
        $items = $this->navItems();
        $searchQuery = trim((string) $request->query('q', ''));
        $requestedPath = $page ? str_replace('..', '', trim($page, '/')) : 'README.md';
        if ($requestedPath !== '' && ! str_ends_with($requestedPath, '.md')) {
            $requestedPath .= '.md';
        }
        $current = $items->firstWhere('path', $requestedPath);
        $docsRoot = base_path('docs');

        if ($searchQuery !== '') {
            $searchResults = $this->searchDocs($searchQuery);

            return view('docs.show', [
                'sections' => self::NAV,
                'currentPath' => null,
                'currentTitle' => '搜索结果',
                'contentHtml' => null,
                'isLanding' => false,
                'landingCards' => collect(),
                'searchQuery' => $searchQuery,
                'searchResults' => $searchResults,
                'searchResultCount' => $searchResults->sum(fn (array $group): int => count($group['items'])),
            ]);
        }

        if ($requestedPath === 'README.md') {
            $readmePath = $docsRoot.DIRECTORY_SEPARATOR.'README.md';
            abort_unless(is_file($readmePath), 404);

            $markdown = $this->normalizeMarkdown(file_get_contents($readmePath) ?: '', 'README.md');
            $html = Str::markdown($markdown);

            return view('docs.show', [
                'sections' => self::NAV,
                'currentPath' => 'README.md',
                'currentTitle' => '开发文档首页',
                'contentHtml' => $html,
                'isLanding' => true,
                'landingCards' => $items->take(6)->values(),
                'searchQuery' => '',
                'searchResults' => collect(),
                'searchResultCount' => 0,
            ]);
        }

        abort_unless($current, 404);

        $filePath = $docsRoot.DIRECTORY_SEPARATOR.$current['path'];
        abort_unless(is_file($filePath), 404);

        $markdown = $this->normalizeMarkdown(file_get_contents($filePath) ?: '', $current['path']);
        $html = Str::markdown($markdown);

        return view('docs.show', [
            'sections' => self::NAV,
            'currentPath' => $current['path'],
            'currentTitle' => $current['title'],
            'contentHtml' => $html,
            'isLanding' => false,
            'landingCards' => collect(),
            'searchQuery' => '',
            'searchResults' => collect(),
            'searchResultCount' => 0,
        ]);
    }

    protected function navItems(): Collection
    {
        return collect(self::NAV)
            ->flatMap(fn (array $section): array => $section['items']);
    }

    protected function normalizeMarkdown(string $markdown, string $currentPath = 'README.md'): string
    {
        $projectRoot = str_replace('\\', '/', base_path());

        $markdown = preg_replace_callback(
            '/\[([^\]]+)\]\(([^)]+)\)/u',
            function (array $matches) use ($currentPath): string {
                $label = $matches[1];
                $target = trim($matches[2]);

                if ($target === '' || Str::startsWith($target, ['http://', 'https://', 'mailto:', '#', '/'])) {
                    return $matches[0];
                }

                [$pathPart, $fragment] = array_pad(explode('#', $target, 2), 2, null);
                $pathPart = trim($pathPart);

                if ($pathPart === '' || ! str_ends_with($pathPart, '.md')) {
                    return $matches[0];
                }

                $resolved = $this->resolveDocPath($currentPath, $pathPart);
                $page = preg_replace('/\.md$/', '', $resolved);
                $url = route('developer.docs', ['page' => $page]);

                if ($fragment) {
                    $url .= '#'.$fragment;
                }

                return sprintf('[%s](%s)', $label, $url);
            },
            $markdown
        ) ?? $markdown;

        $markdown = preg_replace_callback(
            '#\[([^\]]+)\]\('.preg_quote($projectRoot, '#').'/([^)]+)\)#u',
            function (array $matches): string {
                $label = $matches[1];
                $path = ltrim($matches[2], '/');

                return sprintf('%s (`%s/%s`)', $label, self::PROJECT_ROOT_PLACEHOLDER, $path);
            },
            $markdown
        ) ?? $markdown;

        return str_replace($projectRoot, self::PROJECT_ROOT_PLACEHOLDER, $markdown);
    }

    protected function resolveDocPath(string $currentPath, string $target): string
    {
        $baseDir = trim(str_replace('\\', '/', dirname($currentPath)), '/.');
        $combined = $baseDir === '' ? $target : $baseDir.'/'.$target;
        $segments = [];

        foreach (explode('/', str_replace('\\', '/', $combined)) as $segment) {
            if ($segment === '' || $segment === '.') {
                continue;
            }

            if ($segment === '..') {
                array_pop($segments);
                continue;
            }

            $segments[] = $segment;
        }

        return implode('/', $segments);
    }

    protected function searchDocs(string $query): Collection
    {
        $needle = mb_strtolower($query);
        $docsRoot = base_path('docs');

        return collect(self::NAV)
            ->map(function (array $section) use ($docsRoot, $needle, $query): ?array {
                $items = collect($section['items'])
                    ->map(function (array $item) use ($docsRoot, $needle, $query): ?array {
                        $filePath = $docsRoot.DIRECTORY_SEPARATOR.$item['path'];
                        if (! is_file($filePath)) {
                            return null;
                        }

                        $rawMarkdown = file_get_contents($filePath) ?: '';
                        $normalized = $this->normalizeMarkdown($rawMarkdown);
                        $plain = $this->toPlainText($normalized);
                        $title = mb_strtolower($item['title']);
                        $haystack = mb_strtolower($plain);

                        if (! str_contains($title, $needle) && ! str_contains($haystack, $needle)) {
                            return null;
                        }

                        $snippet = $this->buildSnippet($plain, $query);

                        return [
                            'title' => $item['title'],
                            'title_html' => $this->highlightText($item['title'], $query),
                            'path' => $item['path'],
                            'snippet' => $snippet,
                            'snippet_html' => $this->highlightText($snippet, $query),
                        ];
                    })
                    ->filter()
                    ->values();

                if ($items->isEmpty()) {
                    return null;
                }

                return [
                    'section' => $section['section'],
                    'items' => $items->all(),
                ];
            })
            ->filter()
            ->values();
    }

    protected function toPlainText(string $markdown): string
    {
        $text = preg_replace('/`{1,3}.*?`{1,3}/su', ' ', $markdown) ?? $markdown;
        $text = preg_replace('/[#>*\-\[\]\(\)_]/u', ' ', $text) ?? $text;
        $text = strip_tags(Str::markdown($text));

        return trim(preg_replace('/\s+/u', ' ', $text) ?? '');
    }

    protected function buildSnippet(string $text, string $query): string
    {
        $position = mb_stripos($text, $query);

        if ($position === false) {
            return Str::limit($text, 160);
        }

        $start = max(0, $position - 45);
        $snippet = mb_substr($text, $start, 180);

        return ($start > 0 ? '…' : '').trim($snippet).(mb_strlen($text) > ($start + 180) ? '…' : '');
    }

    protected function highlightText(string $text, string $query): string
    {
        $escaped = e($text);
        $needle = trim($query);

        if ($needle === '') {
            return $escaped;
        }

        $pattern = '/('.preg_quote($needle, '/').')/iu';

        return preg_replace($pattern, '<mark>$1</mark>', $escaped) ?? $escaped;
    }
}
