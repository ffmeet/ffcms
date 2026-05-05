<x-filament-panels::page class="ecms-settings-form-page">
    <form wire:submit="save" class="space-y-6">
        <div class="ecms-settings-page-intro">
            <p class="ecms-settings-eyebrow">Payment</p>
            <h2>支付中心</h2>
            <p>这里统一维护支付环境、渠道参数、回调入口和就绪状态，让前台下单、会员支付页和后台订单处理使用同一套规则。</p>
        </div>

        <section class="ecms-settings-overview" aria-label="支付概览">
            @foreach ($this->getSummaryCards() as $card)
                <article class="ecms-settings-overview-card">
                    <p class="ecms-settings-overview-label">{{ $card['label'] }}</p>
                    <strong class="ecms-settings-overview-value">{{ $card['value'] }}</strong>
                    <p class="ecms-settings-overview-copy">{{ $card['description'] }}</p>
                </article>
            @endforeach
        </section>

        <section class="ecms-payment-provider-grid" aria-label="支付渠道状态">
            @foreach ($this->getProviderCards() as $card)
                <article class="ecms-payment-provider-card">
                    <div class="ecms-payment-provider-head">
                        <div>
                            <p class="ecms-payment-provider-eyebrow">{{ strtoupper($card['provider']) }}</p>
                            <h3>{{ $card['label'] }}</h3>
                        </div>
                        <span @class([
                            'ecms-payment-provider-badge',
                            'is-ready' => $card['ready'],
                            'is-warning' => $card['enabled'] && ! $card['ready'],
                        ])>
                            @if (! $card['enabled'])
                                已关闭
                            @elseif ($card['ready'])
                                可结算
                            @else
                                待补齐
                            @endif
                        </span>
                    </div>

                    <dl class="ecms-payment-provider-meta">
                        <div>
                            <dt>环境</dt>
                            <dd>{{ $card['mode'] === 'production' ? 'Production' : 'Sandbox' }}</dd>
                        </div>
                        <div>
                            <dt>参数完整度</dt>
                            <dd>{{ $card['configured_count'] }} / {{ $card['required_count'] }}</dd>
                        </div>
                        <div class="ecms-payment-provider-meta-wide">
                            <dt>Webhook</dt>
                            <dd>{{ $card['notify_url'] }}</dd>
                        </div>
                        <div class="ecms-payment-provider-meta-wide">
                            <dt>缺失项</dt>
                            <dd>
                                @if ($card['missing'] === [])
                                    无
                                @else
                                    {{ implode(' / ', $card['missing']) }}
                                @endif
                            </dd>
                        </div>
                    </dl>
                </article>
            @endforeach
        </section>

        <section class="ecms-payment-ops-grid" aria-label="支付运维说明">
            @foreach ($this->getOperationalNotes() as $note)
                <article class="ecms-payment-ops-card">
                    <h3>{{ $note['title'] }}</h3>
                    <p>{{ $note['body'] }}</p>
                </article>
            @endforeach
        </section>

        {{ $this->form }}

        <div class="ecms-settings-page-actions">
            <x-filament::button type="submit">
                保存支付设置
            </x-filament::button>
        </div>
    </form>
</x-filament-panels::page>
