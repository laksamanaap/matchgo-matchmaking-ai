<?php

namespace App\Filament\Resources;

use App\Filament\Resources\VenueResource\Pages;
use App\Models\Venue;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Toggle;
use Filament\Infolists\Components\IconEntry;
use Filament\Infolists\Components\ImageEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\View;
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

    public static function canAccess(): bool
    {
        return auth()->user()?->hasRole(['admin', 'super_admin']);
    }

    public static function form(Schema $schema): Schema
    {
        return $schema->schema([
            TextInput::make('name')
                ->label('Nama Lapangan')
                ->required()
                ->maxLength(255),

            FileUpload::make('images')
                ->label('Foto Lapangan')
                ->image()
                ->multiple()
                ->reorderable()
                ->appendFiles()
                ->maxFiles(5)
                ->disk('public')
                ->directory('venue-images')
                ->imageEditor()
                ->panelLayout('grid')
                ->columnSpanFull(),

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

            View::make('filament.forms.venue-map')
                ->columnSpanFull(),

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

    public static function infolist(Schema $schema): Schema
    {
        return $schema->components([
            ImageEntry::make('images')
                ->label('Foto Lapangan')
                ->disk('public')
                ->height(180)
                ->columnSpanFull(),

            Grid::make(2)->schema([
                TextEntry::make('name')->label('Nama Lapangan'),
                TextEntry::make('city')->label('Kota'),
                TextEntry::make('address')->label('Alamat')->columnSpanFull(),
                TextEntry::make('price_per_hour')->label('Harga/Jam')->money('IDR'),
                TextEntry::make('contact_phone')->label('Nomor Kontak')->placeholder('-'),
                TextEntry::make('latitude')->label('Latitude'),
                TextEntry::make('longitude')->label('Longitude'),
                IconEntry::make('is_active')->label('Aktif')->boolean(),
            ]),
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
                ViewAction::make(),
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
