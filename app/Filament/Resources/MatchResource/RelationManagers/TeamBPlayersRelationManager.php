<?php

namespace App\Filament\Resources\MatchResource\RelationManagers;

use App\Models\User;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Toggle;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class TeamBPlayersRelationManager extends RelationManager
{
    protected static string $relationship = 'matchPlayers';

    public static function getTitle(\Illuminate\Database\Eloquent\Model $ownerRecord, string $pageClass): string
    {
        return '🔴 Tim B — ' . ($ownerRecord->teamB?->name ?? 'Tim B');
    }

    protected static ?string $label = 'Pemain';

    public function form(Schema $schema): Schema
    {
        $match = $this->getOwnerRecord();

        return $schema->schema([
            Select::make('user_id')
                ->label('Pemain')
                ->options(User::where('role', 'player')->pluck('name', 'id'))
                ->searchable()
                ->required(),

            Hidden::make('team_id')
                ->default($match->team_b_id),

            Toggle::make('attended')
                ->label('Hadir')
                ->default(true),
        ]);
    }

    public function table(Table $table): Table
    {
        $match = $this->getOwnerRecord();

        return $table
            ->recordTitleAttribute('user.name')
            ->modifyQueryUsing(fn (Builder $query) => $query->where('team_id', $match->team_b_id))
            ->columns([
                TextColumn::make('user.name')
                    ->label('Nama Pemain')
                    ->searchable(),

                IconColumn::make('attended')
                    ->label('Hadir')
                    ->boolean()
                    ->trueIcon('heroicon-o-check-circle')
                    ->falseIcon('heroicon-o-x-circle')
                    ->trueColor('success')
                    ->falseColor('danger'),
            ])
            ->headerActions([
                CreateAction::make()
                    ->label('Tambah Pemain Tim B')
                    ->mutateFormDataUsing(function (array $data) use ($match): array {
                        $data['team_id'] = $match->team_b_id;
                        return $data;
                    }),
            ])
            ->actions([
                EditAction::make()->label('Edit'),
                DeleteAction::make()->label('Hapus'),
            ])
            ->bulkActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
