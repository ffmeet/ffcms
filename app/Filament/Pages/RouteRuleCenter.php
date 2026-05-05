<?php

namespace App\Filament\Pages;

use App\Filament\Concerns\UsesSettingsShell;
use App\Models\SiteSetting;
use App\Support\RouteRuleManager;
use Filament\Actions\Action;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;

class RouteRuleCenter extends Page implements HasForms
{
    use InteractsWithForms;
    use UsesSettingsShell;

    protected static bool $shouldRegisterNavigation = false;

    protected static string|\BackedEnum|null $navigationIcon = Heroicon::OutlinedLink;

    protected static ?string $title = '路由';

    protected string $view = 'filament.pages.route-rule-center';

    public ?array $data = [];

    public SiteSetting $record;

    public function mount(): void
    {
        $this->record = SiteSetting::current();
        $this->form->fill($this->routeRuleFormData());
    }

    public function form(Schema $schema): Schema
    {
        return $schema
            ->model($this->record)
            ->statePath('data')
            ->components([
                Section::make('公开入口')
                    ->schema($this->entryFields('public_entries', [
                        'search' => '搜索',
                        'pricing' => '会员计划',
                        'events' => '活动',
                        'shop' => '商店',
                        'member' => '会员中心',
                        'admin' => '后台',
                        'login' => '登录',
                        'register' => '注册',
                        'home' => '前台首页',
                    ]))
                    ->columns(1),
                Section::make('会员中心入口')
                    ->schema($this->entryFields('member_entries', [
                        'dashboard' => '总览',
                        'posts' => '我的稿件',
                        'comments' => '我的评论',
                        'orders' => '我的订单',
                        'subscriptions' => '我的订阅',
                        'create_post' => '发布新稿件',
                        'profile' => '修改资料',
                        'activity_center' => '活动中心',
                        'activities' => '我的活动',
                    ]))
                    ->columns(1),
            ]);
    }

    public function save(): void
    {
        $this->record->fill($this->form->getState());
        $this->record->save();

        Notification::make()
            ->title('入口规则已保存')
            ->success()
            ->send();
    }

    public function getSummaryCards(): array
    {
        return RouteRuleManager::summaryCards($this->effectiveSettings());
    }

    public function getPublicEntries(): array
    {
        return RouteRuleManager::publicEntries($this->effectiveSettings());
    }

    public function getMemberEntries(): array
    {
        return RouteRuleManager::memberEntries($this->effectiveSettings());
    }

    protected function effectiveSettings(): array
    {
        return array_replace_recursive(
            SiteSetting::defaults(),
            $this->record->toArray(),
            $this->data ?? [],
        );
    }

    protected function entryFields(string $group, array $items): array
    {
        return collect($items)
            ->map(function (string $defaultLabel, string $key) use ($group): Grid {
                return Grid::make([
                    'default' => 1,
                    'md' => 3,
                ])->schema([
                    TextInput::make("business_settings.route_settings.{$group}.{$key}.label")
                        ->label($defaultLabel)
                        ->hiddenLabel()
                        ->required()
                        ->extraAttributes(['class' => 'ecms-route-rule-name ecms-settings-input-compact'])
                        ->columnSpan(1),
                    TextInput::make("business_settings.route_settings.{$group}.{$key}.url")
                        ->label('URL')
                        ->hiddenLabel()
                        ->prefix('URL')
                        ->required()
                        ->extraAttributes(['class' => 'ecms-route-rule-url ecms-settings-input-wide'])
                        ->columnSpan([
                            'default' => 1,
                            'md' => 2,
                        ]),
                ])->extraAttributes(['class' => 'ecms-route-rule-form-row']);
            })
            ->all();
    }

    protected function routeRuleFormData(): array
    {
        $data = array_replace_recursive(
            SiteSetting::defaults(),
            $this->record->toArray(),
        );

        foreach (RouteRuleManager::publicEntries($data) as $key => $entry) {
            data_set($data, "business_settings.route_settings.public_entries.{$key}.label", $entry['label']);
            data_set($data, "business_settings.route_settings.public_entries.{$key}.url", $entry['url']);
        }

        foreach (RouteRuleManager::memberEntries($data) as $key => $entry) {
            data_set($data, "business_settings.route_settings.member_entries.{$key}.label", $entry['label']);
            data_set($data, "business_settings.route_settings.member_entries.{$key}.url", $entry['url']);
        }

        return $data;
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
