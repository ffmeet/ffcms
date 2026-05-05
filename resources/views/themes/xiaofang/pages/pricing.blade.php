@extends(\App\Support\SiteTheme::view('layout', 'themes.xiaofang.layout'), ['title' => '会员计划 - ' . ($siteSettings->site_name ?? '小芳侠')])

@section('content')
    @include(\App\Support\SiteTheme::view('components.feature-hero', 'themes.xiaofang.components.feature-hero'), [
        'eyebrow' => 'Membership',
        'title' => '会员计划',
        'description' => '会员体系会作为内容权限、活动优先权和商店权益的统一底座，当前先把套餐展示与订阅入口稳定下来。',
        'actions' => [
            ['label' => '浏览活动', 'url' => route('events.index'), 'variant' => 'primary'],
        ],
        'metrics' => [
            ['label' => '启用套餐', 'value' => $pricingMetrics['active_plans']],
            ['label' => '月付方案', 'value' => $pricingMetrics['monthly_plans']],
            ['label' => '年付方案', 'value' => $pricingMetrics['yearly_plans']],
        ],
    ])

    <section class="mt-12 grid gap-x-10 gap-y-12 md:grid-cols-2 xl:grid-cols-3">
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
            <article class="border-b border-[#ece7e0] pb-8">
                <div class="flex flex-wrap items-center gap-2 text-[11px] font-semibold uppercase tracking-[0.24em] text-[#8b8175]">
                    <span>{{ $billingLabel }}</span>
                    @if ($isCurrentPlan)
                        <span class="text-[#151515]">• 当前生效中</span>
                    @endif
                </div>

                <h2 class="mt-4 font-serif text-[2.1rem] font-semibold leading-[1.15] tracking-tight text-[#151515]">{{ $plan->name }}</h2>

                @if ($plan->description)
                    <p class="mt-4 text-[15px] leading-8 text-[#5f574f]">{{ $plan->description }}</p>
                @endif

                <div class="mt-6 flex items-end gap-3">
                    <span class="text-4xl font-semibold text-[#151515]">¥{{ number_format((float) $plan->price, 2) }}</span>
                    <span class="pb-1 text-sm text-[#8b8175]">/{{ $priceSuffix }}</span>
                </div>
                <div class="mt-2 text-sm text-[#8b8175]">有效期 {{ $plan->duration_days }} 天</div>

                <div class="mt-6 space-y-2 text-sm leading-7 text-[#5f574f]">
                    @forelse ((array) $plan->features as $key => $value)
                        <div>
                            <span class="font-medium text-[#151515]">{{ is_string($key) ? $key : '权益' }}</span>
                            @if (filled($value))
                                <span> · {{ $value }}</span>
                            @endif
                        </div>
                    @empty
                        <div class="text-[#8b8175]">当前套餐的权益说明还在补充中。</div>
                    @endforelse
                </div>

                <div class="mt-6">
                    @auth
                        @if ($isCurrentPlan)
                            <div class="inline-flex border border-[#d8d1c8] bg-white px-5 py-3 text-sm font-semibold text-[#151515]">当前套餐已生效</div>
                        @else
                            <form method="POST" action="{{ route('pricing.subscribe', $plan->slug) }}">
                                @csrf
                                <button type="submit" class="bg-[#151515] px-5 py-3 text-sm font-semibold text-white transition hover:bg-[#2b2b2b]">发起订阅</button>
                            </form>
                        @endif
                    @else
                        <a href="{{ route('login') }}" class="inline-flex bg-[#151515] px-5 py-3 text-sm font-semibold text-white transition hover:bg-[#2b2b2b]">登录后订阅</a>
                    @endauth
                </div>
            </article>
        @empty
            <div class="border border-dashed border-[#d8d1c8] bg-white px-6 py-10 text-sm text-[#78716c] md:col-span-2 xl:col-span-3">
                当前还没有启用中的会员套餐。
            </div>
        @endforelse
    </section>
@endsection
