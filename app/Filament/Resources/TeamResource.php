<?php

namespace App\Filament\Resources;

use App\Filament\Resources\TeamResource\Pages;
use App\Models\Team;
use App\Models\User;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class TeamResource extends Resource
{
    protected static ?string $model = Team::class;

    protected static \UnitEnum|string|null $navigationGroup = 'Laporan & Statistik';
    protected static \BackedEnum|string|null $navigationIcon = 'heroicon-o-user-group';
    protected static ?string $navigationLabel = 'Data Tim';
    protected static ?string $modelLabel = 'Tim';
    protected static ?string $pluralModelLabel = 'Daftar Tim';
    protected static ?int $navigationSort = 1;

    public static function canAccess(): bool
    {
        return auth()->user()?->hasRole(['admin', 'super_admin']);
    }

    public static function form(Schema $schema): Schema
    {
        return $schema->schema([
            Section::make('Informasi Tim')->schema([
                TextInput::make('name')
                    ->label('Nama Tim')
                    ->required()
                    ->maxLength(100),

                Select::make('owner_id')
                    ->label('Owner / Kapten')
                    ->options(User::query()->pluck('name', 'id'))
                    ->searchable()
                    ->required(),

                TextInput::make('city')
                    ->label('Kota')
                    ->required()
                    ->maxLength(100),

                Select::make('skill_level')
                    ->label('Level Skill')
                    ->options([
                        'casual'      => 'Casual',
                        'semi_pro'    => 'Semi Pro',
                        'competitive' => 'Kompetitif',
                    ])
                    ->required(),

                TextInput::make('player_count')
                    ->label('Jumlah Pemain')
                    ->numeric()
                    ->default(5)
                    ->minValue(1)
                    ->maxValue(20),

                TextInput::make('logo_url')
                    ->label('URL Logo (opsional)')
                    ->url()
                    ->nullable(),
            ]),

            Section::make('Lokasi Tim')->schema([
                Grid::make(2)->schema([
                    TextInput::make('latitude')
                        ->label('Latitude')
                        ->numeric()
                        ->required()
                        ->placeholder('-6.200000'),

                    TextInput::make('longitude')
                        ->label('Longitude')
                        ->numeric()
                        ->required()
                        ->placeholder('106.816666'),
                ]),
            ]),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->label('Nama Tim')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('city')
                    ->label('Kota')
                    ->sortable(),

                TextColumn::make('skill_level')
                    ->label('Level')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'casual'      => 'gray',
                        'semi_pro'    => 'warning',
                        'competitive' => 'success',
                        default       => 'gray',
                    })
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'casual'      => 'Casual',
                        'semi_pro'    => 'Semi Pro',
                        'competitive' => 'Kompetitif',
                        default       => $state,
                    }),

                TextColumn::make('owner.name')
                    ->label('Kapten/Owner')
                    ->searchable(),

                TextColumn::make('teamStats.total_matches')
                    ->label('Total Match')
                    ->numeric()
                    ->default(0),

                TextColumn::make('teamStats.wins')
                    ->label('Menang')
                    ->numeric()
                    ->default(0),

                TextColumn::make('player_count')
                    ->label('Pemain')
                    ->numeric(),

                IconColumn::make('verification_status')
                    ->label('Terverifikasi')
                    ->getStateUsing(fn ($record): bool => $record->verification_status === 'verified')
                    ->boolean(),
                    
                TextColumn::make('created_at')
                    ->label('Bergabung')
                    ->date('d M Y')
                    ->sortable(),
            ])
            ->filters([
                SelectFilter::make('skill_level')
                    ->label('Level')
                    ->options([
                        'casual'      => 'Casual',
                        'semi_pro'    => 'Semi Pro',
                        'competitive' => 'Kompetitif',
                    ]),

                SelectFilter::make('city')
                    ->label('Kota')
                    ->options(fn () => Team::query()->distinct()->pluck('city', 'city')->toArray()),
            ])
            ->actions([
                EditAction::make(),
                DeleteAction::make(),
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
        return [
            TeamResource\RelationManagers\TeamMembersRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index'  => Pages\ListTeams::route('/'),
            'create' => Pages\CreateTeam::route('/create'),
            'edit'   => Pages\EditTeam::route('/{record}/edit'),
        ];
    }
}
