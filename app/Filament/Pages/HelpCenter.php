<?php

namespace App\Filament\Pages;

use App\Filament\Concerns\UsesSettingsShell;
use Filament\Pages\Page;
use Filament\Support\Icons\Heroicon;

class HelpCenter extends Page
{
    use UsesSettingsShell;

    protected static bool $shouldRegisterNavigation = false;

    protected static string|\BackedEnum|null $navigationIcon = Heroicon::OutlinedQuestionMarkCircle;

    protected static ?string $title = '帮助中心';

    protected string $view = 'filament.pages.help-center';
}
