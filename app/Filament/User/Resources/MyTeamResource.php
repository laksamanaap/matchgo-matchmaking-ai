<?php

namespace App\Filament\User\Resources;

use App\Enums\SkillLevel;
use App\Enums\VerificationStatus;
use App\Filament\User\Resources\MyTeamResource\Pages;
use App\Filament\User\Resources\MyTeamResource\RelationManagers;
use App\Models\Team;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\BadgeColumn;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class MyTeamResource extends Resource
{
    protected static ?string $model = Team::class;

    protected static \BackedEnum|string|null $navigationIcon = 'heroicon-o-user-group';

    protected static \UnitEnum|string|null $navigationGroup = 'Tim Saya';

    protected static ?string $navigationLabel = 'Tim Saya';

    protected static ?string $modelLabel = 'Tim';

    protected static ?int $navigationSort = 1;

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->where('owner_id', auth()->id());
    }

    public static function form(Schema $schema): Schema
    {
        return $schema->schema([
            Section::make('Informasi Tim')->schema([
                Grid::make(2)->schema([
                    TextInput::make('name')
                        ->label('Nama Tim')
                        ->required()
                        ->maxLength(100),

                    TextInput::make('city')
                        ->label('Kota')
                        ->required()
                        ->maxLength(100),
                ]),

                Grid::make(2)->schema([
                    Select::make('skill_level')
                        ->label('Level Tim')
                        ->options(SkillLevel::options())
                        ->required(),

                    TextInput::make('player_count')
                        ->label('Jumlah Pemain')
                        ->numeric()
                        ->default(5)
                        ->minValue(5)
                        ->maxValue(20),
                ]),

                Grid::make(2)->schema([
                    TextInput::make('latitude')
                        ->label('Latitude')
                        ->numeric()
                        ->placeholder('-6.2088'),

                    TextInput::make('longitude')
                        ->label('Longitude')
                        ->numeric()
                        ->placeholder('106.8456'),
                ]),

                FileUpload::make('logo_url')
                    ->label('Logo Tim')
                    ->image()
                    ->directory('team-logos')
                    ->maxSize(2048)
                    ->acceptedFileTypes(['image/jpeg', 'image/png', 'image/webp']),
            ]),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                ImageColumn::make('logo_url')
                    ->label('Logo')
                    ->circular()
                    ->defaultImageUrl(fn () => 'https://ui-avatars.com/api/?name=T&background=16a34a&color=fff'),

                TextColumn::make('name')
                    ->label('Nama Tim')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('city')
                    ->label('Kota')
                    ->searchable(),

                TextColumn::make('skill_level')
                    ->label('Level')
                    ->badge()
                    ->color(fn (string $state): string => SkillLevel::from($state)->color())
                    ->formatStateUsing(fn (string $state): string => SkillLevel::from($state)->label()),

                TextColumn::make('verification_status')
                    ->label('Status')
                    ->badge()
                    ->color(fn (string $state): string => VerificationStatus::from($state)->color())
                    ->formatStateUsing(fn (string $state): string => VerificationStatus::from($state)->label()),

                TextColumn::make('teamMembers_count')
                    ->label('Anggota')
                    ->counts('teamMembers')
                    ->suffix(' orang'),
            ])
            ->recordActions([
                EditAction::make(),
            ]);
    }

    public static function getRelationManagers(): array
    {
        return [
            RelationManagers\TeamMembersRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index'  => Pages\ListMyTeams::route('/'),
            'create' => Pages\CreateMyTeam::route('/create'),
            'edit'   => Pages\EditMyTeam::route('/{record}/edit'),
        ];
    }
}
