<x-filament-panels::page class="ecms-settings-home-page">
    @php($cards = $this->getSummaryCards())
    @php($operationalCards = $this->getOperationalCards())
    @php($entries = $this->getEntries())
    @php($activeLevel = request('level'))
    @php($activeQuery = request('q'))

    <header class="ecms-settings-header">
        <div>
            <p class="ecms-settings-eyebrow">上传诊断</p>
            <h1>上传记录中心</h1>
            <p>集中查看前台封面、后台媒体库、后台附件创建的上传记录。优先用这里判断是 PHP 上传限制、临时目录异常，还是最终写入失败。</p>
        </div>
    </header>

    <section class="ecms-settings-overview" aria-label="上传诊断概览">
        @foreach ($cards as $card)
            <article class="ecms-settings-overview-card">
                <p class="ecms-settings-overview-label">{{ $card['label'] }}</p>
                <strong class="ecms-settings-overview-value">{{ $card['value'] }}</strong>
                <p class="ecms-settings-overview-copy">{{ $card['description'] }}</p>
            </article>
        @endforeach
    </section>

    <section class="ecms-settings-section">
        <div class="ecms-settings-section-head">
            <h2>入口与流程体检</h2>
        </div>

        <div class="ecms-ops-grid">
            @foreach ($operationalCards as $card)
                <article class="ecms-ops-card">
                    <div class="ecms-ops-card-head">
                        <div>
                            <p class="ecms-ops-card-eyebrow">{{ $card['eyebrow'] }}</p>
                            <h3>{{ $card['title'] }}</h3>
                        </div>
                        <span @class([
                            'ecms-ops-card-badge',
                            'is-warning' => ($card['status'] ?? 'healthy') === 'warning',
                        ])>
                            {{ ($card['status'] ?? 'healthy') === 'warning' ? '关注中' : '正常' }}
                        </span>
                    </div>

                    <p class="ecms-ops-card-copy">{{ $card['summary'] }}</p>

                    <dl class="ecms-ops-card-metrics">
                        @foreach ($card['metrics'] as $metric)
                            <div>
                                <dt>{{ $metric['label'] }}</dt>
                                <dd>{{ $metric['value'] }}</dd>
                            </div>
                        @endforeach
                    </dl>

                    <div class="ecms-ops-card-actions">
                        @foreach ($card['actions'] as $action)
                            <a href="{{ $action['url'] }}" class="ecms-ops-card-link">{{ $action['label'] }}</a>
                        @endforeach
                    </div>
                </article>
            @endforeach
        </div>
    </section>

    <section class="ecms-settings-section">
        <div class="ecms-settings-section-head">
            <h2>筛选</h2>
        </div>

        <form method="GET" class="grid gap-4 md:grid-cols-[180px_minmax(0,1fr)_auto]">
            <label class="block">
                <span class="mb-2 block text-sm font-medium text-gray-700">级别</span>
                <select
                    name="level"
                    class="w-full rounded-xl border border-gray-200 bg-white px-4 py-3 text-sm text-gray-900 outline-none transition focus:border-primary-400"
                >
                    <option value="">全部</option>
                    <option value="error" @selected($activeLevel === 'error')>仅错误</option>
                    <option value="warning" @selected($activeLevel === 'warning')>仅警告</option>
                    <option value="info" @selected($activeLevel === 'info')>仅成功 / 信息</option>
                </select>
            </label>

            <label class="block">
                <span class="mb-2 block text-sm font-medium text-gray-700">搜索</span>
                <input
                    type="text"
                    name="q"
                    value="{{ $activeQuery }}"
                    placeholder="文件名、事件名、用户 ID、错误信息"
                    class="w-full rounded-xl border border-gray-200 bg-white px-4 py-3 text-sm text-gray-900 outline-none transition focus:border-primary-400"
                >
            </label>

            <div class="flex items-end gap-3">
                <button type="submit" class="inline-flex items-center justify-center rounded-full bg-primary-600 px-5 py-3 text-sm font-semibold text-white transition hover:bg-primary-500">
                    筛选
                </button>
                <a href="{{ request()->url() }}" class="inline-flex items-center justify-center rounded-full border border-gray-200 px-5 py-3 text-sm font-medium text-gray-700 transition hover:border-primary-300 hover:text-primary-700">
                    重置
                </a>
            </div>
        </form>
    </section>

    <section class="ecms-settings-section">
        <div class="ecms-settings-section-head">
            <h2>最近上传记录</h2>
        </div>

        @if (filled($entries))
            <div class="overflow-hidden rounded-3xl border border-gray-200 bg-white shadow-sm">
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200 text-sm">
                        <thead class="bg-gray-50 text-left text-xs uppercase tracking-[0.16em] text-gray-500">
                            <tr>
                                <th class="px-4 py-3 font-semibold">时间</th>
                                <th class="px-4 py-3 font-semibold">事件</th>
                                <th class="px-4 py-3 font-semibold">级别</th>
                                <th class="px-4 py-3 font-semibold">文件</th>
                                <th class="px-4 py-3 font-semibold">用户</th>
                                <th class="px-4 py-3 font-semibold">说明</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            @foreach ($entries as $entry)
                                @php($context = $entry['context'])
                                @php($level = $entry['level'])
                                @php($levelClass = match ($level) {
                                    'error' => 'bg-rose-50 text-rose-700 ring-rose-200',
                                    'warning' => 'bg-amber-50 text-amber-700 ring-amber-200',
                                    default => 'bg-emerald-50 text-emerald-700 ring-emerald-200',
                                })
                                <tr class="align-top">
                                    <td class="px-4 py-4 text-gray-600">{{ $entry['timestamp'] }}</td>
                                    <td class="px-4 py-4">
                                        <div class="font-medium text-gray-900">{{ $entry['event'] }}</div>
                                        <div class="mt-1 text-xs text-gray-500">{{ $entry['environment'] }}</div>
                                    </td>
                                    <td class="px-4 py-4">
                                        <span class="inline-flex rounded-full px-2.5 py-1 text-xs font-semibold ring-1 {{ $levelClass }}">
                                            {{ strtoupper($level) }}
                                        </span>
                                    </td>
                                    <td class="px-4 py-4 text-gray-700">
                                        <div>{{ $context['original_name'] ?? $context['filename'] ?? $context['file'] ?? '—' }}</div>
                                        <div class="mt-1 text-xs text-gray-500">{{ $context['mime_type'] ?? '—' }}</div>
                                    </td>
                                    <td class="px-4 py-4 text-gray-700">{{ $context['user_id'] ?? '—' }}</td>
                                    <td class="px-4 py-4">
                                        <div class="text-gray-700">
                                            {{ $context['error_message'] ?? $context['message'] ?? ($context['stored_path'] ?? $context['uploaded_path'] ?? '—') }}
                                        </div>
                                        <div class="mt-2 grid gap-1 text-xs text-gray-500">
                                            @if (filled($context['upload_max_filesize'] ?? null))
                                                <div>PHP 上传上限：{{ $context['upload_max_filesize'] }}</div>
                                            @endif
                                            @if (filled($context['post_max_size'] ?? null))
                                                <div>POST 上限：{{ $context['post_max_size'] }}</div>
                                            @endif
                                            @if (filled($context['size'] ?? null))
                                                <div>大小：{{ number_format((int) $context['size']) }} bytes</div>
                                            @elseif (filled($context['reported_size'] ?? null))
                                                <div>上报大小：{{ number_format((int) $context['reported_size']) }} bytes</div>
                                            @endif
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        @else
            <div class="rounded-3xl border border-dashed border-gray-300 bg-white px-6 py-10 text-sm leading-7 text-gray-500">
                当前筛选条件下还没有上传记录。你可以先做一次前台封面上传或后台媒体上传，再回来查看这里的诊断结果。
            </div>
        @endif
    </section>
</x-filament-panels::page>
