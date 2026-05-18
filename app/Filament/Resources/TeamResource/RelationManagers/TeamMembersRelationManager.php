<?php

namespace App\Filament\Resources\TeamResource\RelationManagers;

use Filament\Actions\AttachAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\DetachAction;
use Filament\Actions\DetachBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Select;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class TeamMembersRelationManager extends RelationManager
{
    protected static string $relationship = 'members';

    protected static ?string $title = 'Anggota Tim';
    protected static ?string $label = 'Anggota';

    public function form(Schema $schema): Schema
    {
        return $schema->schema([
            Select::make('role')
                ->label('Peran')
                ->options([
                    'captain' => 'Kapten',
                    'member'  => 'Anggota',
                ])
                ->default('member')
                ->required(),
        ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('name')
            ->columns([
                TextColumn::make('name')
                    ->label('Nama')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('email')
                    ->label('Email'),

                TextColumn::make('pivot.role')
                    ->label('Peran')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'captain' => 'warning',
                        'member'  => 'gray',
                        default   => 'gray',
                    })
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'captain' => 'Kapten',
                        'member'  => 'Anggota',
                        default   => $state,
                    }),
            ])
            ->headerActions([
                AttachAction::make()
                    ->label('Tambah Anggota')
                    ->preloadRecordSelect()
                    ->recordSelectOptionsQuery(fn ($query) => $query->where('role', 'player'))
                    ->form(fn (AttachAction $action): array => [
                        $action->getRecordSelect()
                            ->label('Pilih Pemain'),
                        Select::make('role')
                            ->label('Peran')
                            ->options([
                                'captain' => 'Kapten',
                                'member'  => 'Anggota',
                            ])
                            ->default('member')
                            ->required(),
                    ]),
            ])
            ->actions([
                EditAction::make()->label('Edit'),
                DetachAction::make()->label('Keluarkan'),
            ])
            ->bulkActions([
                BulkActionGroup::make([
                    DetachBulkAction::make()->label('Keluarkan Semua'),
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
