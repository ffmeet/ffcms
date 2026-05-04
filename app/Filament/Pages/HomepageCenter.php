<?php

namespace App\Filament\Pages;

use App\Filament\Concerns\UsesSettingsShell;
use App\Filament\Resources\SiteSettings\Schemas\SiteSettingForm;
use App\Models\SiteSetting;
use App\Support\FrontendCache;
use App\Support\SiteTheme;
use Filament\Actions\Action;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;

class HomepageCenter extends Page implements HasForms
{
    use InteractsWithForms;
    use UsesSettingsShell;

    protected static bool $shouldRegisterNavigation = false;

    protected static string|\BackedEnum|null $navigationIcon = Heroicon::OutlinedHome;

    protected static ?string $title = '首页设置';

    protected string $view = 'filament.pages.homepage-center';

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
            ->components(SiteSettingForm::homepageComponents($this->activeTheme()));
    }

    public function save(): void
    {
        $state = array_replace_recursive($this->record->toArray(), $this->form->getState());

        $this->record->fill($state);
        $this->record->save();
        FrontendCache::flushAll();

        Notification::make()
            ->title('首页设置已保存')
            ->body('首页位置配置与前台首页缓存已同步刷新。')
            ->success()
            ->send();
    }

    public function activeTheme(): string
    {
        $theme = (string) data_get(
            $this->data,
            'business_settings.active_theme',
            data_get($this->record->business_settings, 'active_theme', 'default')
        );

        return array_key_exists($theme, SiteTheme::THEMES) ? $theme : 'default';
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
