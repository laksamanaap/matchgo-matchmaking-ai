<?php

namespace App\Filament\Resources\MatchRequestResource\Pages;

use App\Filament\Resources\MatchRequestResource;
use Filament\Resources\Pages\ListRecords;

class ListMatchRequests extends ListRecords
{
    protected static string $resource = MatchRequestResource::class;

    protected function getHeaderActions(): array
    {
        return [];
    }
}
