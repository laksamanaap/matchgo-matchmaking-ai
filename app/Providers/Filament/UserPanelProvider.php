<?php

namespace App\Providers\Filament;

use Filament\Http\Middleware\Authenticate;
use Filament\Http\Middleware\AuthenticateSession;
use Filament\Http\Middleware\DisableBladeIconComponents;
use Filament\Http\Middleware\DispatchServingFilamentEvent;
use Filament\Navigation\NavigationGroup;
use Filament\Navigation\NavigationItem;
use App\Filament\User\Pages\EditProfile;
use App\Filament\User\Widgets\RecentMatchesWidget;
use App\Filament\User\Widgets\TeamStatsWidget;
use Filament\Pages\Dashboard;
use Filament\Panel;
use Filament\PanelProvider;
use Filament\Support\Colors\Color;
use Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse;
use Illuminate\Cookie\Middleware\EncryptCookies;
use Illuminate\Foundation\Http\Middleware\PreventRequestForgery;
use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\Session\Middleware\StartSession;
use Illuminate\View\Middleware\ShareErrorsFromSession;

class UserPanelProvider extends PanelProvider
{
    public function panel(Panel $panel): Panel
    {
        return $panel
            ->id('user')
            ->path('app')
            ->login()
            ->profile(EditProfile::class)
            ->brandName('MatchGo')
            ->darkMode(true)
            ->colors([
                'primary' => Color::Blue,
            ])
            ->navigationGroups([
                NavigationGroup::make('Tim Saya'),
                NavigationGroup::make('Pertandingan'),
            ])
            ->navigationItems([
                NavigationItem::make('Pertandingan')
                    ->url('/pertandingan')
                    ->icon('heroicon-o-trophy')
                    ->group('Pertandingan')
                    ->sort(1),

                NavigationItem::make('Cari Lawan Otomatis')
                    ->url('/automatching')
                    ->icon('heroicon-o-magnifying-glass')
                    ->group('Pertandingan')
                    ->sort(2),

                NavigationItem::make('Cari Lapangan')
                    ->url('/venues')
                    ->icon('heroicon-o-map-pin')
                    ->group('Pertandingan')
                    ->sort(3),
            ])
            ->discoverResources(in: app_path('Filament/User/Resources'), for: 'App\\Filament\\User\\Resources')
            ->discoverPages(in: app_path('Filament/User/Pages'), for: 'App\\Filament\\User\\Pages')
            ->discoverWidgets(in: app_path('Filament/User/Widgets'), for: 'App\\Filament\\User\\Widgets')
            ->pages([
                Dashboard::class,
            ])
            ->widgets([
                TeamStatsWidget::class,
                RecentMatchesWidget::class,
            ])
            ->middleware([
                EncryptCookies::class,
                AddQueuedCookiesToResponse::class,
                StartSession::class,
                AuthenticateSession::class,
                ShareErrorsFromSession::class,
                PreventRequestForgery::class,
                SubstituteBindings::class,
                DisableBladeIconComponents::class,
                DispatchServingFilamentEvent::class,
            ])
            ->authMiddleware([
                Authenticate::class,
            ])
            ->databaseNotifications()
            ->databaseNotificationsPolling('30s');
    }
}
