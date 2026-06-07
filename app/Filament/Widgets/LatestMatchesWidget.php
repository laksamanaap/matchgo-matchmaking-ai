<?php

namespace App\Filament\Widgets;

use App\Models\FutsalMatch;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;
use Illuminate\Database\Eloquent\Builder;

class LatestMatchesWidget extends BaseWidget
{
    protected static ?string $heading = 'Pertandingan Mendatang';
    protected static ?int $sort = 2;
    protected int | string | array $columnSpan = 'full';

    public function table(Table $table): Table
    {
        return $table
            ->query(
                FutsalMatch::query()
                    ->whereIn('status', ['scheduled', 'ongoing'])
                    ->with(['teamA', 'teamB', 'venue'])
                    ->latest('match_date')
                    ->limit(5)
            )
            ->columns([
                TextColumn::make('teams')
                    ->label('Pertandingan')
                    ->getStateUsing(fn (FutsalMatch $record): string =>
                        ($record->teamA?->name ?? 'Tim A tidak tersedia') . ' vs ' . ($record->teamB?->name ?? 'Menunggu Lawan')
                    ),

                TextColumn::make('lapangan')
                    ->label('Lapangan')
                    ->getStateUsing(fn (FutsalMatch $record): string => $record->field?->name ?? $record->venue?->name ?? '-')
                    ->placeholder('-'),

                TextColumn::make('match_date')
                    ->label('Tanggal')
                    ->date('d M Y'),

                TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'scheduled' => 'info',
                        'ongoing'   => 'warning',
                        default     => 'gray',
                    })
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'scheduled' => 'Dijadwalkan',
                        'ongoing'   => 'Berlangsung',
                        default     => $state,
                    }),
            ]);
    }
}
