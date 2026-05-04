@extends(\App\Support\SiteTheme::view('layout', 'themes.default.layout'), ['title' => '会员计划 - ' . ($siteSettings->site_name ?? '年度科技先生')])

@section('content')
    @include(\App\Support\SiteTheme::view('components.feature-hero', 'site.partials.feature-hero'), [
        'eyebrow' => 'Membership',
        'title' => '会员计划',
        'description' => '会员体系会作为内容权限、活动优先权和商店权益的统一底座。当前先把套餐展示做稳，后续再接入真正的订阅、支付和续费流程。',
        'actions' => [
            ['label' => '浏览活动', 'url' => route('events.index'), 'variant' => 'primary'],
            ['label' => '浏览商店', 'url' => route('shop.index')],
        ],
        'metrics' => [
            ['label' => '启用套餐', 'value' => $pricingMetrics['active_plans']],
            ['label' => '月付方案', 'value' => $pricingMetrics['monthly_plans']],
            ['label' => '年付方案', 'value' => $pricingMetrics['yearly_plans']],
        ],
    ])

    <section class="mt-8 grid gap-5 lg:grid-cols-3">
        @forelse ($plans as $plan)
            @php
                $billingLabel = match($plan->billing_period) {
                    'monthly' => '月付',
                    'yearly' => '年付',
                    'once' => '一次性',
                    default => '套餐',
                };
                $priceSuffix = $plan->billing_period === 'yearly' ? '年' : ($plan->billing_period === 'monthly' ? '月' : '次');
                $isCurrentPlan = in_array($plan->id, $activePlanIds ?? [], true);
            @endphp
            <article class="site-card p-6">
                <div class="flex flex-wrap gap-2">
                    <span class="site-chip site-chip--brand">{{ $billingLabel }}</span>
                    @if ($isCurrentPlan)
                        <span class="site-chip site-chip--emerald">当前生效中</span>
                    @endif
                </div>
                <h2 class="mt-3 text-3xl font-semibold tracking-tight text-slate-950">{{ $plan->name }}</h2>
                @if ($plan->description)
                    <p class="mt-4 text-sm leading-7 text-slate-600">{{ $plan->description }}</p>
                @endif
                <div class="mt-6 flex items-end gap-3">
                    <span class="text-4xl font-semibold text-slate-950">¥{{ number_format((float) $plan->price, 2) }}</span>
                    <span class="pb-1 text-sm text-slate-500">/{{ $priceSuffix }}</span>
                </div>
                <div class="mt-2 text-sm text-slate-500">有效期 {{ $plan->duration_days }} 天</div>
                <div class="mt-6 space-y-3">
                    @forelse ((array) $plan->features as $key => $value)
                        <div class="rounded-[18px] border border-slate-200 bg-slate-50 px-4 py-3 text-sm text-slate-700">
                            <span class="font-medium">{{ is_string($key) ? $key : '权益' }}</span>
                            @if (filled($value))
                                <span class="text-slate-500"> · {{ $value }}</span>
                            @endif
                        </div>
                    @empty
                        <div class="rounded-[18px] border border-dashed border-slate-300 bg-slate-50 px-4 py-3 text-sm text-slate-500">
                            当前套餐的权益说明还在补充中。
                        </div>
                    @endforelse
                </div>
                <div class="mt-6 flex flex-wrap gap-2">
                    <span class="site-chip site-chip--emerald">支持订阅记录</span>
                    <span class="site-chip site-chip--slate">支付流程预留中</span>
                </div>
                <div class="mt-4">
                    @auth
                        @if ($isCurrentPlan)
                            <div class="w-full rounded-full border border-emerald-200 bg-emerald-50 px-5 py-3 text-center text-sm font-semibold text-emerald-700">当前套餐已生效</div>
                        @else
                            <form method="POST" action="{{ route('pricing.subscribe', $plan->slug) }}">
                                @csrf
                                <button type="submit" class="w-full rounded-full bg-slate-950 px-5 py-3 text-sm font-semibold text-white transition hover:bg-blue-600">发起订阅</button>
                            </form>
                        @endif
                    @else
                        <a href="{{ route('login') }}" class="block w-full rounded-full bg-slate-950 px-5 py-3 text-center text-sm font-semibold text-white transition hover:bg-blue-600">登录后订阅</a>
                    @endauth
                </div>
            </article>
        @empty
            <div class="rounded-[28px] border border-dashed border-slate-300 bg-white/80 px-6 py-10 text-sm text-slate-500 lg:col-span-3">
                当前还没有启用中的会员套餐。
            </div>
        @endforelse
    </section>
@endsection
