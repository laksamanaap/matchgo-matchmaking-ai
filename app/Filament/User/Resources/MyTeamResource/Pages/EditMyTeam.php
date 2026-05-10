<?php

namespace App\Filament\User\Resources\MyTeamResource\Pages;

use App\Filament\User\Resources\MyTeamResource;
use App\Filament\User\Resources\MyTeamResource\RelationManagers\TeamMembersRelationManager;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditMyTeam extends EditRecord
{
    protected static string $resource = MyTeamResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make()->label('Hapus Tim'),
        ];
    }

    public function getRelationManagers(): array
    {
        return [
            TeamMembersRelationManager::class,
        ];
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
