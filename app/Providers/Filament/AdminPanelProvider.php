<?php

namespace App\Providers\Filament;

use App\Http\Middleware\RedirectUnauthorizedAdminSession;
use App\Models\SiteSetting;
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
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
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
            ->brandLogo(function (): HtmlString {
                $settings = Schema::hasTable('site_settings')
                    ? SiteSetting::current()
                    : SiteSetting::make(SiteSetting::defaults());

                $brandName = e($settings->site_name ?: 'FFMeet');
                $brandMark = e($settings->logo_text ?: '帝');
                $brandLogoPath = $settings->admin_logo_path ?: $settings->frontend_logo_path;
                $brandIcon = filled($brandLogoPath)
                    ? e(Storage::disk('public')->url($brandLogoPath))
                    : null;

                return new HtmlString(view('filament.components.admin-brand', [
                    'brandName' => $brandName,
                    'brandMark' => $brandMark,
                    'brandIcon' => $brandIcon,
                ])->render());
            })
            ->brandName(function (): string {
                $settings = Schema::hasTable('site_settings')
                    ? SiteSetting::current()
                    : SiteSetting::make(SiteSetting::defaults());

                return ($settings->site_name ?: 'FFMeet').' 控制台';
            })
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
                PanelsRenderHook::HEAD_END,
                function (): string {
                    $settings = Schema::hasTable('site_settings')
                        ? SiteSetting::current()
                        : SiteSetting::make(SiteSetting::defaults());

                    return view('partials.site-favicons', [
                        'settings' => $settings,
                    ])->render();
                }
            )
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
                RedirectUnauthorizedAdminSession::class,
                Authenticate::class,
            ]);
    }
}
