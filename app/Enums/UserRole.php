<?php

namespace App\Enums;

enum UserRole: string
{
    case Player     = 'player';
    case Admin      = 'admin';
    case Auditor    = 'auditor';
    case SuperAdmin = 'super_admin';

    public function label(): string
    {
        return match($this) {
            self::Player     => 'Pemain',
            self::Admin      => 'Admin',
            self::Auditor    => 'Auditor',
            self::SuperAdmin => 'Super Admin',
        };
    }

    public function canAccessAdminPanel(): bool
    {
        return in_array($this, [self::Admin, self::Auditor, self::SuperAdmin]);
    }

    public function canAccessUserPanel(): bool
    {
        return $this === self::Player;
    }

    public static function options(): array
    {
        return collect(self::cases())
            ->mapWithKeys(fn ($case) => [$case->value => $case->label()])
            ->toArray();
    }
}
