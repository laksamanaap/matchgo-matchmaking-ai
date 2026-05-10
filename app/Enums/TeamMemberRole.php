<?php

namespace App\Enums;

enum TeamMemberRole: string
{
    case Captain = 'captain';
    case Member  = 'member';

    public function label(): string
    {
        return match($this) {
            self::Captain => 'Kapten',
            self::Member  => 'Anggota',
        };
    }

    public static function options(): array
    {
        return collect(self::cases())
            ->mapWithKeys(fn ($case) => [$case->value => $case->label()])
            ->toArray();
    }
}
