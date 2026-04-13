<?php

namespace App\Filament\Resources;

use App\Filament\Resources\TeamVerificationResource\Pages;
use App\Models\TeamVerification;
use App\Notifications\TeamVerificationRejected;
use App\Services\TeamStatsService;
use Filament\Actions\Action;
use Filament\Forms\Components\Textarea;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class TeamVerificationResource extends Resource
{
    protected static ?string $model = TeamVerification::class;

    protected static \UnitEnum|string|null    $navigationGroup = 'Audit & Verifikasi';
    protected static \BackedEnum|string|null  $navigationIcon  = 'heroicon-o-shield-check';
    protected static ?string $navigationLabel = 'Verifikasi Tim';
    protected static ?string $modelLabel      = 'Verifikasi Tim';
    protected static ?string $pluralModelLabel = 'Verifikasi Tim';
    protected static ?int $navigationSort     = 1;

    public static function canAccess(): bool
    {
        return auth()->user()?->hasRole(['auditor', 'super_admin']);
    }

    public static function form(Schema $schema): Schema
    {
        return $schema->schema([]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->query(TeamVerification::query()->with(['team.owner', 'team']))
            ->columns([
                TextColumn::make('team.name')
                    ->label('Tim')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('team.owner.name')
                    ->label('Owner')
                    ->searchable(),

                TextColumn::make('team.skill_level')
                    ->label('Level')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'casual'      => 'gray',
                        'semi_pro'    => 'warning',
                        'competitive' => 'danger',
                        default       => 'gray',
                    }),

                TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'pending'  => 'warning',
                        'verified' => 'success',
                        'rejected' => 'danger',
                        default    => 'gray',
                    }),

                TextColumn::make('created_at')
                    ->label('Tanggal')
                    ->dateTime('d M Y')
                    ->sortable(),
            ])
            ->actions([
                Action::make('verifikasi')
                    ->label('Verifikasi')
                    ->icon('heroicon-o-check-circle')
                    ->color('success')
                    ->requiresConfirmation()
                    ->modalHeading('Verifikasi Tim')
                    ->modalDescription('Apakah kamu yakin ingin memverifikasi tim ini?')
                    ->visible(fn (TeamVerification $record): bool => $record->status === 'pending')
                    ->action(function (TeamVerification $record): void {
                        $record->update([
                            'status'     => 'verified',
                            'auditor_id' => auth()->id(),
                        ]);
                        $record->team->update(['verification_status' => 'verified']);
                    }),

                Action::make('tolak')
                    ->label('Tolak')
                    ->icon('heroicon-o-x-circle')
                    ->color('danger')
                    ->visible(fn (TeamVerification $record): bool => $record->status === 'pending')
                    ->form([
                        Textarea::make('notes')
                            ->label('Alasan Penolakan')
                            ->required()
                            ->rows(3),
                    ])
                    ->action(function (TeamVerification $record, array $data): void {
                        $record->update([
                            'status'     => 'rejected',
                            'notes'      => $data['notes'],
                            'auditor_id' => auth()->id(),
                        ]);
                        $record->team->update(['verification_status' => 'rejected']);
                        $record->team->owner->notify(
                            new TeamVerificationRejected($record->team, $data['notes'])
                        );
                    }),
            ])
            ->defaultSort('created_at', 'desc');
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListTeamVerifications::route('/'),
        ];
    }
}
