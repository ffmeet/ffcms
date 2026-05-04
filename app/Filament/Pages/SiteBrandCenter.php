<?php

namespace App\Filament\Pages;

use App\Filament\Concerns\UsesSettingsShell;
use App\Filament\Resources\SiteSettings\Schemas\SiteSettingForm;
use App\Models\SiteSetting;
use App\Support\SiteIconManager;
use Filament\Actions\Action;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;

class SiteBrandCenter extends Page implements HasForms
{
    use InteractsWithForms;
    use UsesSettingsShell;

    protected static bool $shouldRegisterNavigation = false;

    protected static string|\BackedEnum|null $navigationIcon = Heroicon::OutlinedIdentification;

    protected static ?string $title = '站点品牌';

    protected string $view = 'filament.pages.site-brand-center';

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
            ->components([
                SiteSettingForm::brandSection(),
            ]);
    }

    public function save(): void
    {
        $this->record->fill($this->form->getState());
        $this->record->save();

        app(SiteIconManager::class)->regenerate($this->record);

        Notification::make()
            ->title('站点品牌已保存')
            ->success()
            ->send();
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
