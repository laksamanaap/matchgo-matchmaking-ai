<?php

namespace App\Filament\Resources;

use App\Filament\Resources\VenueResource\Pages;
use App\Models\Venue;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Toggle;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;

class VenueResource extends Resource
{
    protected static ?string $model = Venue::class;

    protected static \UnitEnum|string|null $navigationGroup = 'Manajemen Lapangan';
    protected static \BackedEnum|string|null $navigationIcon = 'heroicon-o-map-pin';
    protected static ?string $navigationLabel = 'Lapangan';
    protected static ?string $modelLabel = 'Lapangan';
    protected static ?string $pluralModelLabel = 'Daftar Lapangan';
    protected static ?int $navigationSort = 1;

    public static function form(Schema $schema): Schema
    {
        return $schema->schema([
            TextInput::make('name')
                ->label('Nama Lapangan')
                ->required()
                ->maxLength(255),

            Textarea::make('address')
                ->label('Alamat')
                ->required()
                ->rows(3),

            TextInput::make('city')
                ->label('Kota')
                ->required()
                ->maxLength(100),

            Grid::make(2)->schema([
                TextInput::make('latitude')
                    ->label('Latitude')
                    ->numeric()
                    ->required(),

                TextInput::make('longitude')
                    ->label('Longitude')
                    ->numeric()
                    ->required(),
            ]),

            TextInput::make('price_per_hour')
                ->label('Harga per Jam')
                ->numeric()
                ->prefix('Rp')
                ->required(),

            TextInput::make('contact_phone')
                ->label('Nomor Kontak')
                ->tel()
                ->maxLength(20),

            Toggle::make('is_active')
                ->label('Aktif')
                ->default(true),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->label('Nama Lapangan')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('city')
                    ->label('Kota')
                    ->sortable(),

                TextColumn::make('price_per_hour')
                    ->label('Harga/Jam')
                    ->money('IDR')
                    ->sortable(),

                IconColumn::make('is_active')
                    ->label('Aktif')
                    ->boolean(),

                TextColumn::make('venue_schedules_count')
                    ->label('Total Slot')
                    ->counts('venueSchedules')
                    ->badge(),
            ])
            ->filters([
                SelectFilter::make('city')
                    ->label('Kota')
                    ->options(fn () => Venue::query()->distinct()->pluck('city', 'city')->toArray()),

                TernaryFilter::make('is_active')
                    ->label('Status Aktif'),
            ])
            ->actions([
                EditAction::make(),
                DeleteAction::make(),
            ])
            ->bulkActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index'  => Pages\ListVenues::route('/'),
            'create' => Pages\CreateVenue::route('/create'),
            'edit'   => Pages\EditVenue::route('/{record}/edit'),
        ];
    }
}
