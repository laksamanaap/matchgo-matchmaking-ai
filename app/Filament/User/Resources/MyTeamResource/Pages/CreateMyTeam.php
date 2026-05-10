<?php

namespace App\Filament\User\Resources\MyTeamResource\Pages;

use App\Filament\User\Resources\MyTeamResource;
use Filament\Resources\Pages\CreateRecord;

class CreateMyTeam extends CreateRecord
{
    protected static string $resource = MyTeamResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['owner_id'] = auth()->id();

        return $data;
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
