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
                ->descriptionIcon('heroicon-m-user-group', 'before')
                ->chart($this->trend(Team::query()))
                ->color('success'),

            Stat::make('Pertandingan Selesai', FutsalMatch::where('status', 'completed')->count())
                ->description('Match yang telah selesai')
                ->descriptionIcon('heroicon-m-trophy', 'before')
                ->chart($this->trend(FutsalMatch::where('status', 'completed')))
                ->color('warning'),

            Stat::make('Lapangan Aktif', Venue::where('is_active', true)->count())
                ->description('Lapangan tersedia')
                ->descriptionIcon('heroicon-m-map-pin', 'before')
                ->chart($this->trend(Venue::where('is_active', true)))
                ->color('info'),

            Stat::make('Request Pending', MatchRequest::where('status', 'pending')->count())
                ->description('Menunggu konfirmasi')
                ->descriptionIcon('heroicon-m-clock', 'before')
                ->chart($this->trend(MatchRequest::where('status', 'pending')))
                ->color('danger'),
        ];
    }

    /**
     * Jumlah record per hari untuk 7 hari terakhir (untuk sparkline).
     */
    private function trend(\Illuminate\Contracts\Database\Eloquent\Builder $query): array
    {
        $counts = [];

        for ($i = 6; $i >= 0; $i--) {
            $counts[] = (clone $query)
                ->whereDate('created_at', now()->subDays($i)->toDateString())
                ->count();
        }

        return $counts;
    }
}
