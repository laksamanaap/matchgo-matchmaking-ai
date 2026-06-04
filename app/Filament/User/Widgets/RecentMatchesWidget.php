<?php

namespace App\Filament\User\Widgets;

use App\Models\FutsalMatch;
use App\Models\Team;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;
use Illuminate\Database\Eloquent\Builder;

class RecentMatchesWidget extends BaseWidget
{
    protected static ?int $sort = 2;

    protected int | string | array $columnSpan = 'full';

    protected static ?string $heading = 'Riwayat Pertandingan Terakhir';

    public function table(Table $table): Table
    {
        $teamIds = Team::where('owner_id', auth()->id())->pluck('id');

        return $table
            ->query(
                FutsalMatch::with(['teamA', 'teamB', 'venue'])
                    ->where(function (Builder $q) use ($teamIds) {
                        $q->whereIn('team_a_id', $teamIds)
                          ->orWhereIn('team_b_id', $teamIds);
                    })
                    ->whereIn('status', ['completed', 'scheduled', 'ongoing'])
                    ->latest('match_date')
            )
            ->paginated([5, 10, 25])
            ->defaultPaginationPageOption(5)
            ->columns([
                Tables\Columns\TextColumn::make('match_date')
                    ->label('Tanggal')
                    ->date('d M Y')
                    ->sortable(),

                Tables\Columns\TextColumn::make('teams')
                    ->label('Pertandingan')
                    ->state(fn (FutsalMatch $record): string =>
                        ($record->teamA?->name ?? '—') . ' vs ' . ($record->teamB?->name ?? '—')
                    ),

                Tables\Columns\TextColumn::make('score')
                    ->label('Skor')
                    ->state(fn (FutsalMatch $record): string =>
                        $record->score_a !== null
                            ? $record->score_a . ' — ' . $record->score_b
                            : '—'
                    )
                    ->alignCenter(),

                Tables\Columns\TextColumn::make('result')
                    ->label('Hasil')
                    ->state(function (FutsalMatch $record): string {
                        $teamIds = Team::where('owner_id', auth()->id())->pluck('id');

                        if ($record->score_a === null || $record->score_b === null) {
                            return '—';
                        }

                        $isA = $teamIds->contains($record->team_a_id);
                        $my  = $isA ? $record->score_a : $record->score_b;
                        $opp = $isA ? $record->score_b : $record->score_a;

                        return match (true) {
                            $my > $opp => 'Menang',
                            $my < $opp => 'Kalah',
                            default    => 'Seri',
                        };
                    })
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'Menang' => 'success',
                        'Kalah'  => 'danger',
                        'Seri'   => 'warning',
                        default  => 'gray',
                    }),

                Tables\Columns\TextColumn::make('venue.name')
                    ->label('Lapangan')
                    ->limit(25),

                Tables\Columns\TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'scheduled' => 'Terjadwal',
                        'ongoing'   => 'Berlangsung',
                        'completed' => 'Selesai',
                        'cancelled' => 'Dibatalkan',
                        default     => $state,
                    })
                    ->color(fn (string $state): string => match ($state) {
                        'scheduled' => 'info',
                        'ongoing'   => 'warning',
                        'completed' => 'success',
                        'cancelled' => 'gray',
                        default     => 'gray',
                    }),
            ]);
    }
}
