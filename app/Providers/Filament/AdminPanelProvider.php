<?php

namespace App\Providers\Filament;

use Filament\Http\Middleware\Authenticate;
use Filament\Http\Middleware\AuthenticateSession;
use Filament\Http\Middleware\DisableBladeIconComponents;
use Filament\Http\Middleware\DispatchServingFilamentEvent;
use Filament\Panel;
use Filament\PanelProvider;
use Filament\Pages\Dashboard;
use Filament\Support\Colors\Color;
use Filament\View\PanelsRenderHook;
use Filament\Widgets\AccountWidget;
use Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse;
use Illuminate\Cookie\Middleware\EncryptCookies;
use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken;
use Illuminate\Support\Facades\Blade;
use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\Session\Middleware\StartSession;
use Illuminate\Support\HtmlString;
use Illuminate\View\Middleware\ShareErrorsFromSession;
use Slimani\MediaManager\MediaManagerPlugin;
use Tilto\Commentable\CommentablePlugin;

class AdminPanelProvider extends PanelProvider
{
    public function panel(Panel $panel): Panel
    {
        return $panel
            ->default()
            ->id('admin')
            ->path('admin')
            ->brandLogo(new HtmlString(<<<'HTML'
<div
    x-data="{}"
    x-bind:style="$store.sidebar.isOpen ? '--ecms-brand-width: calc(var(--sidebar-width) - 5.45rem)' : '--ecms-brand-width: calc(var(--collapsed-sidebar-width) - 2.55rem)'"
    class="ecms-brand-shell"
>
    <span
        x-show="$store.sidebar.isOpen"
        x-cloak
        class="ecms-brand-full"
    >
        <span class="ecms-brand-mark">帝</span>
        <span class="ecms-brand-copy">
            <span class="ecms-brand-title">帝国 CMS</span>
        </span>
    </span>

    <span
        x-show="! $store.sidebar.isOpen"
        x-cloak
        class="ecms-brand-compact"
    >
        帝
    </span>
</div>
HTML))
            ->brandName('帝国 CMS 控制台')
            ->homeUrl(fn (): string => url('/admin'))
            ->login()
            ->viteTheme('resources/css/filament/admin/theme.css')
            ->colors([
                'primary' => Color::Blue,
                'success' => Color::Emerald,
            ])
            ->sidebarWidth('17rem')
            ->sidebarCollapsibleOnDesktop()
            ->sidebarFullyCollapsibleOnDesktop()
            ->collapsedSidebarWidth('5rem')
            ->renderHook(
                PanelsRenderHook::TOPBAR_LOGO_AFTER,
                fn (): string => Blade::render("@include('filament.components.topbar-nav')")
            )
            ->renderHook(
                PanelsRenderHook::GLOBAL_SEARCH_AFTER,
                fn (): string => Blade::render("@include('filament.components.topbar-publish')")
            )
            ->renderHook(
                PanelsRenderHook::GLOBAL_SEARCH_AFTER,
                fn (): string => Blade::render("@include('filament.components.topbar-notifications')")
            )
            ->plugin(
                MediaManagerPlugin::make()
                    ->mediaManagerPage(\App\Filament\Media\Pages\MediaManager::class)
                    ->navigationGroup('工作流')
                    ->navigationLabel('媒体')
                    ->navigationSort(2)
            )
            ->plugin(CommentablePlugin::make())
            ->pages([
                Dashboard::class,
            ])
            ->discoverResources(in: app_path('Filament/Resources'), for: 'App\Filament\Resources')
            ->discoverPages(in: app_path('Filament/Pages'), for: 'App\Filament\Pages')
            ->discoverWidgets(in: app_path('Filament/Widgets'), for: 'App\Filament\Widgets')
            ->widgets([
                AccountWidget::class,
            ])
            ->middleware([
                EncryptCookies::class,
                AddQueuedCookiesToResponse::class,
                StartSession::class,
                AuthenticateSession::class,
                ShareErrorsFromSession::class,
                VerifyCsrfToken::class,
                SubstituteBindings::class,
                DisableBladeIconComponents::class,
                DispatchServingFilamentEvent::class,
            ])
            ->authMiddleware([
                Authenticate::class,
            ]);
    }
}
