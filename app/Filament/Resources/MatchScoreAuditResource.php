<?php

namespace App\Filament\Resources;

use App\Filament\Resources\MatchScoreAuditResource\Pages;
use App\Models\MatchScoreAudit;
use App\Services\TeamStatsService;
use Filament\Actions\Action;
use Filament\Forms\Components\Textarea;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class MatchScoreAuditResource extends Resource
{
    protected static ?string $model = MatchScoreAudit::class;

    protected static \UnitEnum|string|null    $navigationGroup = 'Audit & Verifikasi';
    protected static \BackedEnum|string|null  $navigationIcon  = 'heroicon-o-clipboard-document-check';
    protected static ?string $navigationLabel = 'Audit Skor';
    protected static ?string $modelLabel      = 'Audit Skor';
    protected static ?string $pluralModelLabel = 'Audit Skor';
    protected static ?int $navigationSort     = 2;

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
            ->query(
                MatchScoreAudit::query()
                    ->with(['futsalMatch.teamA', 'futsalMatch.teamB', 'futsalMatch.venue'])
                    ->whereHas('futsalMatch', fn ($q) => $q->where('status', 'completed'))
            )
            ->columns([
                TextColumn::make('pertandingan')
                    ->label('Pertandingan')
                    ->getStateUsing(
                        fn (MatchScoreAudit $record): string =>
                            $record->futsalMatch->teamA->name . ' vs ' . $record->futsalMatch->teamB->name
                    )
                    ->searchable(false),

                TextColumn::make('skor')
                    ->label('Skor')
                    ->getStateUsing(
                        fn (MatchScoreAudit $record): string =>
                            ($record->futsalMatch->score_a ?? '-') . ' - ' . ($record->futsalMatch->score_b ?? '-')
                    ),

                TextColumn::make('futsalMatch.match_date')
                    ->label('Tanggal')
                    ->date('d M Y')
                    ->sortable(),

                TextColumn::make('status')
                    ->label('Status Audit')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'pending'  => 'warning',
                        'approved' => 'success',
                        'disputed' => 'danger',
                        default    => 'gray',
                    }),

                TextColumn::make('created_at')
                    ->label('Dibuat')
                    ->dateTime('d M Y')
                    ->sortable(),
            ])
            ->actions([
                Action::make('setujui_skor')
                    ->label('Setujui Skor')
                    ->icon('heroicon-o-check-circle')
                    ->color('success')
                    ->requiresConfirmation()
                    ->modalHeading('Setujui Skor')
                    ->modalDescription('Skor akan dikonfirmasi dan statistik tim akan diperbarui.')
                    ->visible(fn (MatchScoreAudit $record): bool => $record->status === 'pending')
                    ->action(function (MatchScoreAudit $record): void {
                        $record->update([
                            'status'     => 'approved',
                            'auditor_id' => auth()->id(),
                        ]);
                        app(TeamStatsService::class)->updateFromMatch($record->futsalMatch);
                    }),

                Action::make('disputed')
                    ->label('Disputed')
                    ->icon('heroicon-o-exclamation-triangle')
                    ->color('danger')
                    ->visible(fn (MatchScoreAudit $record): bool => $record->status === 'pending')
                    ->form([
                        Textarea::make('notes')
                            ->label('Keterangan Dispute')
                            ->required()
                            ->rows(3),
                    ])
                    ->action(function (MatchScoreAudit $record, array $data): void {
                        $record->update([
                            'status'     => 'disputed',
                            'notes'      => $data['notes'],
                            'auditor_id' => auth()->id(),
                        ]);
                        $record->futsalMatch->update(['status' => 'ongoing']);
                    }),
            ])
            ->defaultSort('created_at', 'desc');
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListMatchScoreAudits::route('/'),
        ];
    }
}
