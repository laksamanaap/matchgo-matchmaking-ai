<?php

namespace App\Filament\User\Resources\MyTeamResource\RelationManagers;

use App\Enums\TeamMemberRole;
use App\Models\User;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Forms\Components\Select;
use Filament\Tables\Table;
use Filament\Tables\Columns\TextColumn;
use Filament\Actions\CreateAction;
use Filament\Actions\EditAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\Action;

class TeamMembersRelationManager extends RelationManager
{
    protected static string $relationship = 'teamMembers';

    protected static ?string $title = 'Anggota Tim';

    public function form(Schema $schema): Schema
    {
        return $schema->schema([
            Select::make('user_id')
                ->label('Pemain')
                ->options(function () {
                    $existingIds = $this->getOwnerRecord()->teamMembers()->pluck('user_id');
                    return User::where('role', 'player')
                        ->whereNotIn('id', $existingIds)
                        ->orderBy('name')
                        ->pluck('name', 'id');
                })
                ->searchable()
                ->preload()
                ->required()
                ->disabledOn('edit'),

            Select::make('role')
                ->label('Posisi dalam Tim')
                ->options(TeamMemberRole::options())
                ->default(TeamMemberRole::Member->value)
                ->required(),
        ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('user.name')
            ->columns([
                TextColumn::make('user.name')
                    ->label('Nama Pemain')
                    ->searchable()
                    ->sortable()
                    ->weight('semibold'),

                TextColumn::make('user.email')
                    ->label('Email')
                    ->searchable()
                    ->color('gray'),

                TextColumn::make('role')
                    ->label('Posisi')
                    ->badge()
                    ->color(fn (string $state): string => match($state) {
                        'captain' => 'warning',
                        default   => 'gray',
                    })
                    ->formatStateUsing(fn (string $state): string => TeamMemberRole::from($state)->label()),
            ])
            ->headerActions([
                CreateAction::make()
                    ->label('+ Tambah Anggota')
                    ->modalHeading('Tambah Anggota Tim'),
            ])
            ->recordActions([
                EditAction::make()
                    ->label('Edit Posisi')
                    ->modalHeading('Ubah Posisi Anggota'),

                Action::make('promote')
                    ->label('Jadikan Kapten')
                    ->icon('heroicon-o-star')
                    ->color('warning')
                    ->requiresConfirmation()
                    ->modalHeading('Jadikan Kapten?')
                    ->modalDescription('Anggota ini akan dijadikan kapten tim.')
                    ->visible(fn ($record) => $record->role !== 'captain')
                    ->action(fn ($record) => $record->update(['role' => 'captain'])),

                DeleteAction::make()
                    ->label('Keluarkan')
                    ->modalHeading('Keluarkan anggota?')
                    ->modalDescription('Anggota ini akan dikeluarkan dari tim.'),
            ])
            ->emptyStateHeading('Belum ada anggota')
            ->emptyStateDescription('Tambahkan pemain ke dalam tim kamu.')
            ->emptyStateIcon('heroicon-o-user-group');
    }
}
