<?php

namespace App\Filament\Widgets;

use App\Models\FutsalMatch;
use App\Models\MatchRequest;
use App\Models\Team;
use App\Models\Venue;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class StatsOverviewWidget extends BaseWidget
{
    protected static ?int $sort = 1;

    protected function getStats(): array
    {
        return [
            Stat::make('Total Tim', Team::count())
                ->description('Tim terdaftar')
                ->descriptionIcon('heroicon-o-user-group')
                ->color('primary'),

            Stat::make('Pertandingan Selesai', FutsalMatch::where('status', 'completed')->count())
                ->description('Match yang telah selesai')
                ->descriptionIcon('heroicon-o-trophy')
                ->color('success'),

            Stat::make('Lapangan Aktif', Venue::where('is_active', true)->count())
                ->description('Lapangan tersedia')
                ->descriptionIcon('heroicon-o-map-pin')
                ->color('info'),

            Stat::make('Request Pending', MatchRequest::where('status', 'pending')->count())
                ->description('Menunggu konfirmasi')
                ->descriptionIcon('heroicon-o-paper-airplane')
                ->color('warning'),
        ];
    }
}
