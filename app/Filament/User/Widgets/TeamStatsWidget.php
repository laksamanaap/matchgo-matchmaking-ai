<?php

namespace App\Filament\User\Widgets;

use App\Models\FutsalMatch;
use App\Models\Team;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class TeamStatsWidget extends BaseWidget
{
    protected static ?int $sort = 1;

    protected function getStats(): array
    {
        $user = auth()->user();

        // Aggregate stats across all teams owned by this captain.
        $teamIds = Team::where('owner_id', $user->id)->pluck('id');

        if ($teamIds->isEmpty()) {
            return [
                Stat::make('Tim', '—')->description('Belum punya tim'),
            ];
        }

        $totalWins   = 0;
        $totalLosses = 0;
        $totalDraws  = 0;
        $totalGoals  = 0;
        $totalConceded = 0;

        foreach ($teamIds as $teamId) {
            $matches = FutsalMatch::where(function ($q) use ($teamId) {
                $q->where('team_a_id', $teamId)
                  ->orWhere('team_b_id', $teamId);
            })
            ->where('status', 'completed')
            ->whereNotNull('score_a')
            ->whereNotNull('score_b')
            ->get();

            foreach ($matches as $m) {
                $isA = $m->team_a_id === $teamId;
                $myScore  = $isA ? $m->score_a : $m->score_b;
                $oppScore = $isA ? $m->score_b : $m->score_a;

                $totalGoals    += $myScore;
                $totalConceded += $oppScore;

                if ($myScore > $oppScore) {
                    $totalWins++;
                } elseif ($myScore < $oppScore) {
                    $totalLosses++;
                } else {
                    $totalDraws++;
                }
            }
        }

        $totalPlayed = $totalWins + $totalLosses + $totalDraws;
        $winRate = $totalPlayed > 0
            ? round(($totalWins / $totalPlayed) * 100) . '%'
            : '—';

        return [
            Stat::make('Menang', $totalWins)
                ->description('Total kemenangan')
                ->descriptionIcon('heroicon-m-trophy')
                ->color('success'),

            Stat::make('Kalah', $totalLosses)
                ->description('Total kekalahan')
                ->descriptionIcon('heroicon-m-x-circle')
                ->color('danger'),

            Stat::make('Seri', $totalDraws)
                ->description('Total seri')
                ->descriptionIcon('heroicon-m-minus-circle')
                ->color('warning'),

            Stat::make('Win Rate', $winRate)
                ->description("{$totalPlayed} pertandingan dimainkan")
                ->descriptionIcon('heroicon-m-chart-bar')
                ->color('info'),

            Stat::make('Gol Dicetak', $totalGoals)
                ->description("Kemasukan: {$totalConceded}")
                ->descriptionIcon('heroicon-m-fire')
                ->color('primary'),
        ];
    }
}
