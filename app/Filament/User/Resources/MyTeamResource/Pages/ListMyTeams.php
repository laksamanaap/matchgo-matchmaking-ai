<?php

namespace App\Filament\User\Resources\MyTeamResource\Pages;

use App\Filament\User\Resources\MyTeamResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListMyTeams extends ListRecords
{
    protected static string $resource = MyTeamResource::class;

    protected function getHeaderActions(): array
    {
        // A captain already owns a team — hide the create button once they have one.
        $hasTeam = auth()->user()->ownedTeams()->exists();

        return $hasTeam ? [] : [
            CreateAction::make()->label('Buat Tim Baru'),
        ];
    }
}
