<?php

namespace App\Filament\Pages;

use App\Filament\Concerns\UsesSettingsShell;
use App\Filament\Resources\SiteSettings\Schemas\SiteSettingForm;
use App\Models\SiteSetting;
use App\Support\PaymentProviderRegistry;
use Filament\Actions\Action;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;

class PaymentCenter extends Page implements HasForms
{
    use InteractsWithForms;
    use UsesSettingsShell;

    protected static bool $shouldRegisterNavigation = false;

    protected static string|\BackedEnum|null $navigationIcon = Heroicon::OutlinedCreditCard;

    protected static ?string $title = '支付中心';

    protected string $view = 'filament.pages.payment-center';

    public ?array $data = [];

    public SiteSetting $record;

    public function mount(): void
    {
        $this->record = SiteSetting::current();
        $this->form->fill($this->record->toArray());
    }

    public function form(Schema $schema): Schema
    {
        return $schema
            ->model($this->record)
            ->statePath('data')
            ->components(SiteSettingForm::paymentComponents());
    }

    public function save(): void
    {
        $this->record->fill($this->form->getState());
        $this->record->save();

        Notification::make()
            ->title('支付中心已保存')
            ->success()
            ->send();
    }

    public function getSummaryCards(): array
    {
        $settings = $this->effectiveBusinessSettings();
        $enabled = PaymentProviderRegistry::enabledProviders($settings);
        $ready = PaymentProviderRegistry::readyProviders($settings);

        return [
            [
                'label' => '已启用渠道',
                'value' => (string) count($enabled),
                'description' => $enabled !== [] ? collect($enabled)->map(fn (string $provider) => PaymentProviderRegistry::label($provider))->implode(' / ') : '当前还没有启用在线渠道。',
            ],
            [
                'label' => '可用于结算',
                'value' => (string) count($ready),
                'description' => $ready !== [] ? collect($ready)->map(fn (string $provider) => PaymentProviderRegistry::label($provider))->implode(' / ') : '关键参数未补齐时会自动回退到人工渠道。',
            ],
            [
                'label' => '默认下单渠道',
                'value' => PaymentProviderRegistry::label(PaymentProviderRegistry::defaultProvider($settings)),
                'description' => '商品、会员和活动创建待支付记录时，会优先使用这条渠道规则。',
            ],
        ];
    }

    public function getProviderCards(): array
    {
        $settings = $this->effectiveBusinessSettings();

        return collect(PaymentProviderRegistry::definitions())
            ->reject(fn (array $definition, string $provider): bool => $provider === 'manual')
            ->map(function (array $definition, string $provider) use ($settings): array {
                $enabled = PaymentProviderRegistry::isEnabled($provider, $settings);
                $ready = PaymentProviderRegistry::isReady($provider, $settings);
                $missing = PaymentProviderRegistry::missingCredentials($provider, $settings);

                return [
                    'provider' => $provider,
                    'label' => $definition['label'],
                    'enabled' => $enabled,
                    'ready' => $ready,
                    'mode' => data_get($settings, 'payment_mode', 'sandbox'),
                    'notify_url' => route('payments.webhook', $provider),
                    'missing' => array_values($missing),
                    'configured_count' => count($definition['credentials']) - count($missing),
                    'required_count' => count($definition['credentials']),
                ];
            })
            ->values()
            ->all();
    }

    public function getOperationalNotes(): array
    {
        $settings = $this->effectiveBusinessSettings();
        $mode = data_get($settings, 'payment_mode', 'sandbox');

        return [
            [
                'title' => '回调地址',
                'body' => '每个在线渠道都已经预留独立 webhook 地址，便于后续接真实网关、签名校验和支付回写。',
            ],
            [
                'title' => '结算门槛',
                'body' => '只有启用且关键参数补齐的渠道，才会在会员支付页作为可选方式出现；否则自动回退到人工渠道。',
            ],
            [
                'title' => '当前环境',
                'body' => $mode === 'production'
                    ? '当前处于正式环境，请确保商户号、密钥和 webhook 来源校验都已完成。'
                    : '当前仍是演练环境，适合先把后台参数、回调地址和支付状态流转跑通。',
            ],
        ];
    }

    protected function effectiveBusinessSettings(): array
    {
        return array_replace_recursive(
            SiteSetting::defaults()['business_settings'] ?? [],
            $this->record->business_settings ?? [],
            data_get($this->data, 'business_settings', [])
        );
    }

    protected function getHeaderActions(): array
    {
        return [
            Action::make('back')
                ->label('返回设置中心')
                ->url(SettingsCenter::getUrl())
                ->color('gray'),
        ];
    }
}
