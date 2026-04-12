<?php

namespace App\Filament\Resources\VenueScheduleResource\Pages;

use App\Filament\Resources\VenueScheduleResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListVenueSchedules extends ListRecords
{
    protected static string $resource = VenueScheduleResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
