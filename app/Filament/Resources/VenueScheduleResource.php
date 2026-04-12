<?php

namespace App\Filament\Resources;

use App\Filament\Resources\VenueScheduleResource\Pages;
use App\Models\Venue;
use App\Models\VenueSchedule;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TimePicker;
use Filament\Forms\Components\Toggle;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class VenueScheduleResource extends Resource
{
    protected static ?string $model = VenueSchedule::class;

    protected static \UnitEnum|string|null $navigationGroup = 'Manajemen Lapangan';
    protected static \BackedEnum|string|null $navigationIcon = 'heroicon-o-calendar';
    protected static ?string $navigationLabel = 'Jadwal Lapangan';
    protected static ?string $modelLabel = 'Jadwal Lapangan';
    protected static ?string $pluralModelLabel = 'Jadwal Lapangan';
    protected static ?int $navigationSort = 2;

    public static function form(Schema $schema): Schema
    {
        return $schema->schema([
            Select::make('venue_id')
                ->label('Lapangan')
                ->options(Venue::query()->where('is_active', true)->pluck('name', 'id'))
                ->searchable()
                ->required(),

            DatePicker::make('date')
                ->label('Tanggal')
                ->required(),

            Grid::make(2)->schema([
                TimePicker::make('start_time')
                    ->label('Jam Mulai')
                    ->required(),

                TimePicker::make('end_time')
                    ->label('Jam Selesai')
                    ->required(),
            ]),

            Toggle::make('is_booked')
                ->label('Sudah Dipesan')
                ->default(false),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('venue.name')
                    ->label('Lapangan')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('date')
                    ->label('Tanggal')
                    ->date('d M Y')
                    ->sortable(),

                TextColumn::make('start_time')
                    ->label('Jam Mulai'),

                TextColumn::make('end_time')
                    ->label('Jam Selesai'),

                IconColumn::make('is_booked')
                    ->label('Dipesan')
                    ->boolean(),
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
            ->defaultSort('date', 'desc');
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index'  => Pages\ListVenueSchedules::route('/'),
            'create' => Pages\CreateVenueSchedule::route('/create'),
            'edit'   => Pages\EditVenueSchedule::route('/{record}/edit'),
        ];
    }
}
