<?php

namespace App\Filament\Resources;

use App\Filament\Resources\MatchRequestResource\Pages;
use App\Models\FutsalMatch;
use App\Models\MatchRequest;
use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class MatchRequestResource extends Resource
{
    protected static ?string $model = MatchRequest::class;

    protected static \UnitEnum|string|null $navigationGroup = 'Manajemen Pertandingan';
    protected static \BackedEnum|string|null $navigationIcon = 'heroicon-o-paper-airplane';
    protected static ?string $navigationLabel = 'Pengajuan Match';
    protected static ?string $modelLabel = 'Pengajuan Match';
    protected static ?string $pluralModelLabel = 'Daftar Pengajuan Match';
    protected static ?int $navigationSort = 2;

    public static function canAccess(): bool
    {
        return auth()->user()?->hasRole(['admin', 'super_admin']);
    }

    public static function canCreate(): bool
    {
        return false;
    }

    public static function form(Schema $schema): Schema
    {
        return $schema->schema([]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('requesterTeam.name')
                    ->label('Tim Pemohon')
                    ->searchable(),

                TextColumn::make('opponentTeam.name')
                    ->label('Tim Lawan')
                    ->searchable(),

                TextColumn::make('preferred_date')
                    ->label('Tanggal Diinginkan')
                    ->date('d M Y'),

                TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'pending'   => 'warning',
                        'accepted'  => 'success',
                        'rejected'  => 'danger',
                        'cancelled' => 'gray',
                        default     => 'gray',
                    })
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'pending'   => 'Menunggu',
                        'accepted'  => 'Diterima',
                        'rejected'  => 'Ditolak',
                        'cancelled' => 'Dibatalkan',
                        default     => $state,
                    }),

                TextColumn::make('created_at')
                    ->label('Tanggal Ajuan')
                    ->date('d M Y')
                    ->sortable(),
            ])
            ->actions([
                Action::make('terima')
                    ->label('Terima')
                    ->icon('heroicon-o-check-circle')
                    ->color('success')
                    ->visible(fn (MatchRequest $record): bool => $record->status === 'pending')
                    ->requiresConfirmation()
                    ->action(function (MatchRequest $record) {
                        $record->update(['status' => 'accepted']);

                        FutsalMatch::create([
                            'match_request_id' => $record->id,
                            'venue_id'         => 1,
                            'team_a_id'        => $record->requester_team_id,
                            'team_b_id'        => $record->opponent_team_id,
                            'match_date'       => $record->preferred_date,
                            'start_time'       => '08:00:00',
                            'duration_minutes' => 90,
                            'status'           => 'scheduled',
                        ]);

                        Notification::make()
                            ->title('Pengajuan diterima, pertandingan dibuat!')
                            ->success()
                            ->send();
                    }),

                Action::make('tolak')
                    ->label('Tolak')
                    ->icon('heroicon-o-x-circle')
                    ->color('danger')
                    ->visible(fn (MatchRequest $record): bool => $record->status === 'pending')
                    ->requiresConfirmation()
                    ->action(function (MatchRequest $record) {
                        $record->update(['status' => 'rejected']);

                        Notification::make()
                            ->title('Pengajuan telah ditolak.')
                            ->warning()
                            ->send();
                    }),
            ])
            ->bulkActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('created_at', 'desc');
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListMatchRequests::route('/'),
        ];
    }
}
