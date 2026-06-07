<?php

namespace App\Filament\Resources;

use App\Filament\Resources\MatchResource\Pages;
use App\Models\Field;
use App\Models\FutsalMatch;
use App\Models\MatchRequest;
use App\Models\Team;
use App\Services\MatchCostService;
use App\Services\MatchmakingService;
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

    public static function canCreate(): bool
    {
        return auth()->user()?->hasRole(['admin', 'super_admin']);
    }

    public static function canEdit($record): bool
    {
        return auth()->user()?->hasRole(['admin', 'super_admin']);
    }

    public static function canDelete($record): bool
    {
        return auth()->user()?->hasRole(['admin', 'super_admin']);
    }

    public static function canDeleteAny(): bool
    {
        return auth()->user()?->hasRole(['admin', 'super_admin']);
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
                    ->nullable()
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
                Select::make('field_id')
                    ->label('Lapangan')
                    ->options(Field::query()->where('is_available', true)->pluck('name', 'id'))
                    ->searchable()
                    ->required(fn (string $operation): bool => $operation === 'create')
                    ->helperText('Pilih lapangan dari data Fields. Data lama yang masih memakai Venue tetap bisa tampil.'),

                DatePicker::make('match_date')
                    ->label('Tanggal Pertandingan')
                    ->default(fn (): string => app(MatchmakingService::class)->defaultMatchTime()->toDateString())
                    ->required(),

                TimePicker::make('start_time')
                    ->label('Waktu Mulai')
                    ->default(fn (): string => app(MatchmakingService::class)->defaultMatchTime()->format('H:i:s'))
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
                    'pending'   => 'Menunggu Konfirmasi',
                    'confirmed' => 'Dikonfirmasi',
                    'ongoing'   => 'Berlangsung',
                    'completed' => 'Selesai',
                    'cancelled' => 'Dibatalkan',
                    'expired'   => 'Kedaluwarsa',
                ])
                ->default('scheduled')
                ->required(),

            Section::make('Rincian Biaya')
                ->description('Biaya dihitung otomatis dari lapangan dan durasi pertandingan.')
                ->schema([
                    Grid::make(4)->schema([
                        Placeholder::make('total_cost')
                            ->label('Total Biaya')
                            ->content(fn ($record) => $record?->matchCost
                                ? 'Rp ' . number_format($record->matchCost->total_cost, 0, ',', '.')
                                : '-'
                            ),

                        Placeholder::make('cost_per_team')
                            ->label('Per Tim')
                            ->content(fn ($record) => $record?->matchCost
                                ? 'Rp ' . number_format($record->matchCost->cost_per_team, 0, ',', '.')
                                : '-'
                            ),

                        Placeholder::make('dp_per_team')
                            ->label('DP Minimal')
                            ->content(fn ($record) => $record?->matchCost
                                ? 'Rp ' . number_format($record->matchCost->dp_per_team, 0, ',', '.')
                                : '-'
                            ),

                        Placeholder::make('handling_fee')
                            ->label('Biaya Penanganan')
                            ->content(fn ($record) => $record?->matchCost
                                ? 'Rp ' . number_format($record->matchCost->handling_fee, 0, ',', '.')
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
                        ($record->teamA?->name ?? 'Tim A tidak tersedia') . ' vs ' . ($record->teamB?->name ?? 'Menunggu Lawan')
                    )
                    ->searchable(query: function ($query, string $search) {
                        $query->whereHas('teamA', fn ($q) => $q->where('name', 'like', "%{$search}%"))
                              ->orWhereHas('teamB', fn ($q) => $q->where('name', 'like', "%{$search}%"));
                    }),

                TextColumn::make('lapangan')
                    ->label('Lapangan')
                    ->getStateUsing(fn (FutsalMatch $record): string => $record->field?->name ?? $record->venue?->name ?? '-')
                    ->placeholder('-'),

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
                        'pending'   => 'warning',
                        'confirmed' => 'success',
                        'ongoing'   => 'warning',
                        'completed' => 'success',
                        'cancelled' => 'danger',
                        'expired'   => 'gray',
                        default     => 'gray',
                    })
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'scheduled' => 'Dijadwalkan',
                        'pending'   => 'Menunggu Konfirmasi',
                        'confirmed' => 'Dikonfirmasi',
                        'ongoing'   => 'Berlangsung',
                        'completed' => 'Selesai',
                        'cancelled' => 'Dibatalkan',
                        'expired'   => 'Kedaluwarsa',
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
                        'pending'   => 'Menunggu Konfirmasi',
                        'confirmed' => 'Dikonfirmasi',
                        'ongoing'   => 'Berlangsung',
                        'completed' => 'Selesai',
                        'cancelled' => 'Dibatalkan',
                        'expired'   => 'Kedaluwarsa',
                    ]),
            ])
            ->actions([
                Action::make('hitung_biaya')
                    ->label('Hitung Biaya')
                    ->icon('heroicon-o-calculator')
                    ->color('warning')
                    ->visible(fn (): bool => auth()->user()?->hasRole(['admin', 'super_admin']))
                    ->requiresConfirmation()
                    ->action(function (FutsalMatch $record) {
                        try {
                            app(MatchCostService::class)->calculate($record);
                            Notification::make()
                                ->title('Biaya berhasil dihitung!')
                                ->success()
                                ->send();
                        } catch (\RuntimeException $exception) {
                            Notification::make()
                                ->title('Biaya gagal dihitung')
                                ->body($exception->getMessage())
                                ->danger()
                                ->send();
                        }
                    }),

                Action::make('input_skor')
                    ->label('Input Skor')
                    ->icon('heroicon-o-pencil-square')
                    ->color('success')
                    ->visible(fn (FutsalMatch $record): bool =>
                        $record->team_a_id !== null
                        && $record->team_b_id !== null
                        && in_array($record->status, ['pending', 'scheduled', 'confirmed', 'ongoing'], true)
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
                    DeleteBulkAction::make()
                        ->visible(fn (): bool => auth()->user()?->hasRole(['admin', 'super_admin'])),
                ])
                    ->visible(fn (): bool => auth()->user()?->hasRole(['admin', 'super_admin'])),
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
