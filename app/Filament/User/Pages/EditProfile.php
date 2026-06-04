<?php

namespace App\Filament\User\Pages;

use Filament\Auth\Pages\EditProfile as BaseEditProfile;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Component;
use Filament\Schemas\Schema;

class EditProfile extends BaseEditProfile
{
    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                $this->getNameFormComponent(),
                $this->getEmailFormComponent(),
                TextInput::make('whatsapp')
                    ->label('Nomor WhatsApp')
                    ->placeholder('contoh: 6281234567890')
                    ->helperText('Nomor internasional tanpa + (misal: 6281234567890). Digunakan kapten lawan untuk menghubungimu.')
                    ->tel()
                    ->maxLength(20),
                $this->getPasswordFormComponent(),
                $this->getPasswordConfirmationFormComponent(),
                $this->getCurrentPasswordFormComponent(),
            ]);
    }
}
