<?php

namespace App\Filament\Widgets;

use App\Models\MatchmakingHistory;
use App\Models\MatchmakingMatch;
use App\Models\MatchmakingQueue;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class MatchmakingQueueWidget extends BaseWidget
{
    protected static ?int $sort = 3;

    protected ?string $pollingInterval = '5s';

    protected function getStats(): array
    {
        $activeQueues = MatchmakingQueue::where('status', 'waiting')->count();

        $matchesLast24h = MatchmakingMatch::where('created_at', '>=', now()->subDay())->count();

        $avgWaitSeconds = (int) round(
            MatchmakingHistory::where('result', 'accepted')
                ->where('created_at', '>=', now()->subDay())
                ->avg('queue_duration_seconds') ?? 0
        );

        $avgWaitLabel = $avgWaitSeconds > 0
            ? sprintf('%d:%02d', intdiv($avgWaitSeconds, 60), $avgWaitSeconds % 60)
            : '—';

        return [
            Stat::make('Antrian Aktif', $activeQueues)
                ->description('Tim sedang mencari lawan')
                ->descriptionIcon('heroicon-o-bolt')
                ->color($activeQueues > 0 ? 'warning' : 'gray')
                ->chart($this->queueTrend()),

            Stat::make('Match Ditemukan (24h)', $matchesLast24h)
                ->description('Total pasangan dibuat')
                ->descriptionIcon('heroicon-o-puzzle-piece')
                ->color('primary'),

            Stat::make('Rata-rata Tunggu (24h)', $avgWaitLabel)
                ->description('Sebelum dapat lawan')
                ->descriptionIcon('heroicon-o-clock')
                ->color('success'),
        ];
    }

    /** @return array<int, int> */
    private function queueTrend(): array
    {
        $rows = MatchmakingQueue::where('created_at', '>=', now()->subHours(12))
            ->selectRaw('HOUR(created_at) as hour, COUNT(*) as total')
            ->groupBy('hour')
            ->pluck('total', 'hour');

        $trend = [];
        for ($i = 11; $i >= 0; $i--) {
            $h = (int) now()->subHours($i)->format('H');
            $trend[] = (int) ($rows[$h] ?? 0);
        }
        return $trend;
    }
}
