<?php

namespace App\Filament\Pages;

use App\Filament\Concerns\UsesSettingsShell;
use App\Filament\Resources\SiteSettings\Schemas\SiteSettingForm;
use App\Models\SiteSetting;
use Filament\Actions\Action;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;

class NavigationMenuCenter extends Page implements HasForms
{
    use InteractsWithForms;
    use UsesSettingsShell;

    protected static bool $shouldRegisterNavigation = false;

    protected static string|\BackedEnum|null $navigationIcon = Heroicon::OutlinedBars3BottomLeft;

    protected static ?string $title = '导航菜单';

    protected string $view = 'filament.pages.navigation-menu-center';

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
                SiteSettingForm::navigationSection(),
            ]);
    }

    public function save(): void
    {
        $this->record->fill($this->form->getState());
        $this->record->save();

        Notification::make()
            ->title('菜单项已保存')
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
