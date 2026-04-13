<?php

namespace App\Filament\Resources;

use App\Filament\Resources\MatchResource\Pages;
use App\Models\FutsalMatch;
use App\Models\MatchRequest;
use App\Models\Team;
use App\Models\Venue;
use App\Services\MatchCostService;
use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\TimePicker;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class MatchResource extends Resource
{
    protected static ?string $model = FutsalMatch::class;

    protected static \UnitEnum|string|null $navigationGroup = 'Manajemen Pertandingan';
    protected static \BackedEnum|string|null $navigationIcon = 'heroicon-o-trophy';
    protected static ?string $navigationLabel = 'Pertandingan';
    protected static ?string $modelLabel = 'Pertandingan';
    protected static ?string $pluralModelLabel = 'Daftar Pertandingan';
    protected static ?int $navigationSort = 1;

    public static function canAccess(): bool
    {
        return auth()->user()?->hasRole(['admin', 'auditor', 'super_admin']);
    }

    public static function form(Schema $schema): Schema
    {
        return $schema->schema([
            Section::make('Detail Tim')->schema([
                Select::make('team_a_id')
                    ->label('Tim A')
                    ->options(Team::query()->pluck('name', 'id'))
                    ->searchable()
                    ->required()
                    ->different('team_b_id'),

                Select::make('team_b_id')
                    ->label('Tim B')
                    ->options(Team::query()->pluck('name', 'id'))
                    ->searchable()
                    ->required()
                    ->different('team_a_id'),

                Select::make('match_request_id')
                    ->label('Match Request (Opsional)')
                    ->options(
                        MatchRequest::query()
                            ->where('status', 'accepted')
                            ->with(['requesterTeam', 'opponentTeam'])
                            ->get()
                            ->mapWithKeys(fn ($mr) => [
                                $mr->id => "{$mr->requesterTeam->name} vs {$mr->opponentTeam->name} ({$mr->preferred_date->format('d M Y')})",
                            ])
                    )
                    ->searchable()
                    ->nullable(),
            ]),

            Section::make('Jadwal & Lokasi')->schema([
                Select::make('venue_id')
                    ->label('Lapangan')
                    ->options(Venue::query()->where('is_active', true)->pluck('name', 'id'))
                    ->searchable()
                    ->required(),

                DatePicker::make('match_date')
                    ->label('Tanggal Pertandingan')
                    ->required()
                    ->minDate(now()->toDateString()),

                TimePicker::make('start_time')
                    ->label('Waktu Mulai')
                    ->required(),

                Select::make('duration_minutes')
                    ->label('Durasi')
                    ->options([
                        60  => '60 menit',
                        90  => '90 menit',
                        120 => '120 menit',
                    ])
                    ->default(90)
                    ->required(),
            ]),

            Section::make('Skor')->schema([
                Grid::make(2)->schema([
                    TextInput::make('score_a')
                        ->label('Skor Tim A')
                        ->numeric()
                        ->minValue(0),

                    TextInput::make('score_b')
                        ->label('Skor Tim B')
                        ->numeric()
                        ->minValue(0),
                ]),
            ])->visible(fn (Get $get): bool => $get('status') === 'completed'),

            Select::make('status')
                ->label('Status')
                ->options([
                    'scheduled' => 'Dijadwalkan',
                    'ongoing'   => 'Berlangsung',
                    'completed' => 'Selesai',
                    'cancelled' => 'Dibatalkan',
                ])
                ->default('scheduled')
                ->required(),

            Section::make('Rincian Biaya')
                ->description('Biaya dihitung otomatis. Klik "Hitung Biaya" di halaman daftar pertandingan untuk memperbarui.')
                ->schema([
                    Grid::make(3)->schema([
                        Placeholder::make('total_cost')
                            ->label('Total Biaya')
                            ->hint(fn ($record) => $record?->venue
                                ? 'Rp ' . number_format($record->venue->price_per_hour, 0, ',', '.') . '/jam × ' . $record->duration_minutes . ' menit'
                                : null
                            )
                            ->hintIcon('heroicon-o-information-circle')
                            ->content(fn ($record) => $record?->matchCost
                                ? 'Rp ' . number_format($record->matchCost->total_cost, 0, ',', '.')
                                : '-'
                            ),

                        Placeholder::make('cost_per_team')
                            ->label('Per Tim')
                            ->hint('Total Biaya dibagi 2 tim secara rata')
                            ->hintIcon('heroicon-o-information-circle')
                            ->content(fn ($record) => $record?->matchCost
                                ? 'Rp ' . number_format($record->matchCost->cost_per_team, 0, ',', '.')
                                : '-'
                            ),

                        Placeholder::make('cost_per_player')
                            ->label('Per Pemain')
                            ->hint(function ($record) {
                                if (! $record?->matchCost) return null;
                                $hadir = $record->matchPlayers()
                                    ->where('team_id', $record->team_a_id)
                                    ->where('attended', true)
                                    ->count();
                                return $hadir > 0
                                    ? "Biaya Tim ÷ {$hadir} pemain hadir (Tim A)"
                                    : 'Berdasarkan jumlah pemain terdaftar';
                            })
                            ->hintIcon('heroicon-o-information-circle')
                            ->content(fn ($record) => $record?->matchCost
                                ? 'Rp ' . number_format($record->matchCost->cost_per_player, 0, ',', '.')
                                : '-'
                            ),
                    ]),
                ])
                ->visible(fn ($record) => $record !== null),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('teams')
                    ->label('Pertandingan')
                    ->getStateUsing(fn (FutsalMatch $record): string =>
                        "{$record->teamA->name} vs {$record->teamB->name}"
                    )
                    ->searchable(query: function ($query, string $search) {
                        $query->whereHas('teamA', fn ($q) => $q->where('name', 'like', "%{$search}%"))
                              ->orWhereHas('teamB', fn ($q) => $q->where('name', 'like', "%{$search}%"));
                    }),

                TextColumn::make('venue.name')
                    ->label('Lapangan'),

                TextColumn::make('match_date')
                    ->label('Tanggal')
                    ->date('d M Y')
                    ->sortable(),

                TextColumn::make('start_time')
                    ->label('Jam Mulai'),

                TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'scheduled' => 'info',
                        'ongoing'   => 'warning',
                        'completed' => 'success',
                        'cancelled' => 'danger',
                        default     => 'gray',
                    })
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'scheduled' => 'Dijadwalkan',
                        'ongoing'   => 'Berlangsung',
                        'completed' => 'Selesai',
                        'cancelled' => 'Dibatalkan',
                        default     => $state,
                    }),

                TextColumn::make('matchCost.total_cost')
                    ->label('Total Biaya')
                    ->money('IDR')
                    ->default('-'),
            ])
            ->filters([
                SelectFilter::make('status')
                    ->label('Status')
                    ->options([
                        'scheduled' => 'Dijadwalkan',
                        'ongoing'   => 'Berlangsung',
                        'completed' => 'Selesai',
                        'cancelled' => 'Dibatalkan',
                    ]),
            ])
            ->actions([
                Action::make('hitung_biaya')
                    ->label('Hitung Biaya')
                    ->icon('heroicon-o-calculator')
                    ->color('warning')
                    ->requiresConfirmation()
                    ->action(function (FutsalMatch $record) {
                        app(MatchCostService::class)->calculate($record);
                        Notification::make()
                            ->title('Biaya berhasil dihitung!')
                            ->success()
                            ->send();
                    }),

                Action::make('input_skor')
                    ->label('Input Skor')
                    ->icon('heroicon-o-pencil-square')
                    ->color('success')
                    ->visible(fn (FutsalMatch $record): bool =>
                        in_array($record->status, ['scheduled', 'ongoing'])
                        && auth()->user()?->hasRole(['auditor', 'super_admin'])
                    )
                    ->form([
                        Grid::make(2)->schema([
                            TextInput::make('score_a')
                                ->label('Skor Tim A')
                                ->numeric()
                                ->required()
                                ->minValue(0),

                            TextInput::make('score_b')
                                ->label('Skor Tim B')
                                ->numeric()
                                ->required()
                                ->minValue(0),
                        ]),
                    ])
                    ->action(function (FutsalMatch $record, array $data) {
                        $record->update([
                            'score_a' => $data['score_a'],
                            'score_b' => $data['score_b'],
                            'status'  => 'completed',
                        ]);
                        Notification::make()
                            ->title('Skor berhasil diinput!')
                            ->success()
                            ->send();
                    }),

                EditAction::make()
                    ->visible(fn (): bool => auth()->user()?->hasRole(['admin', 'super_admin'])),
                DeleteAction::make()
                    ->visible(fn (): bool => auth()->user()?->hasRole(['admin', 'super_admin'])),
            ])
            ->bulkActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('match_date', 'desc');
    }

    public static function getRelations(): array
    {
        return [
            MatchResource\RelationManagers\TeamAPlayersRelationManager::class,
            MatchResource\RelationManagers\TeamBPlayersRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index'  => Pages\ListMatches::route('/'),
            'create' => Pages\CreateMatch::route('/create'),
            'edit'   => Pages\EditMatch::route('/{record}/edit'),
        ];
    }
}
