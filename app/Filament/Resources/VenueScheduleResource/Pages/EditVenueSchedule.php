<?php

namespace App\Filament\Resources\VenueScheduleResource\Pages;

use App\Filament\Resources\VenueScheduleResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditVenueSchedule extends EditRecord
{
    protected static string $resource = VenueScheduleResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
