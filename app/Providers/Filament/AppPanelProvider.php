<?php

namespace App\Providers\Filament;

use App\Filament\Pages\CreateOrganization;
use App\Filament\Pages\EditOrganization;
use App\Models\Organization;
use Asmit\ResizedColumn\ResizedColumnPlugin;
use Filament\Http\Middleware\Authenticate;
use Filament\Http\Middleware\AuthenticateSession;
use Filament\Http\Middleware\DisableBladeIconComponents;
use Filament\Http\Middleware\DispatchServingFilamentEvent;
use Filament\Pages\Dashboard;
use Filament\Panel;
use Filament\PanelProvider;
use Filament\Support\Colors\Color;
use Filament\View\PanelsRenderHook;
use Filament\Widgets\AccountWidget;
use Filament\Widgets\FilamentInfoWidget;
use Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse;
use Illuminate\Cookie\Middleware\EncryptCookies;
use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken;
use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\Session\Middleware\StartSession;
use Illuminate\Support\Facades\Blade;
use Illuminate\View\Middleware\ShareErrorsFromSession;

class AppPanelProvider extends PanelProvider
{
    public function panel(Panel $panel): Panel
    {
        return $panel
            ->default()
            ->id('app')
            ->path('app')
            ->spa()
            ->login()
            ->brandName('FinanciApp')
            ->tenant(Organization::class, 'slug')
            ->tenantRegistration(CreateOrganization::class)
            ->tenantProfile(EditOrganization::class)
            ->searchableTenantMenu()
            ->tenantMenu(false)
            ->viteTheme('resources/css/filament/app/theme.css')
            ->colors([
                'primary' => Color::hex('#a4bf3b'),
                'success' => Color::hex('#a4bf3b'),
                'warning' => Color::hex('#ffb029'),
                'danger'  => Color::hex('#ff6b6b'),
                'gray'    => Color::Stone,
                'info'    => Color::Sky,
            ])
            ->collapsibleNavigationGroups()
            // ->sidebarCollapsibleOnDesktop()
            ->sidebarWidth('15rem')
            ->discoverResources(in: app_path('Filament/Resources'), for: 'App\Filament\Resources')
            ->discoverPages(in: app_path('Filament/Pages'), for: 'App\Filament\Pages')
            ->pages([
                \App\Filament\Pages\Home::class,
            ])
            ->discoverWidgets(in: app_path('Filament/Widgets'), for: 'App\Filament\Widgets')
            ->widgets([
                AccountWidget::class,
                FilamentInfoWidget::class,
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
            ])
            ->renderHook(
            PanelsRenderHook::TOPBAR_LOGO_AFTER,
            fn (): string => Blade::render('filament.components.tenant-menu')
            )
            ->font('DM Sans')
            ->maxContentWidth('full')
            ->plugins([
            ]);
    }
}
