<?php

namespace App\Filament\User\Resources\MyTeamResource\RelationManagers;

use App\Enums\TeamMemberRole;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Forms\Components\TextInput;
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
            TextInput::make('name')
                ->label('Nama Pemain')
                ->placeholder('Nama lengkap pemain')
                ->required()
                ->maxLength(100),

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
            ->recordTitleAttribute('name')
            ->columns([
                TextColumn::make('name')
                    ->label('Nama Pemain')
                    ->searchable()
                    ->sortable()
                    ->weight('semibold')
                    ->default('—'),

                TextColumn::make('role')
                    ->label('Posisi')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
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
                    ->label('Edit')
                    ->modalHeading('Ubah Data Anggota'),

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
            ->emptyStateDescription('Tambahkan nama pemain ke dalam tim kamu.')
            ->emptyStateIcon('heroicon-o-user-group');
    }
}
