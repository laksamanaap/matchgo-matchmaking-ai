<?php

namespace App\Enums;

enum SkillLevel: string
{
    case Casual      = 'casual';
    case SemiPro     = 'semi_pro';
    case Competitive = 'competitive';

    public function label(): string
    {
        return match($this) {
            self::Casual      => 'Kasual',
            self::SemiPro     => 'Semi Pro',
            self::Competitive => 'Kompetitif',
        };
    }

    public function color(): string
    {
        return match($this) {
            self::Casual      => 'success',
            self::SemiPro     => 'warning',
            self::Competitive => 'danger',
        };
    }

    public function tier(): int
    {
        return match($this) {
            self::Casual      => 0,
            self::SemiPro     => 1,
            self::Competitive => 2,
        };
    }

    public static function options(): array
    {
        return collect(self::cases())
            ->mapWithKeys(fn ($case) => [$case->value => $case->label()])
            ->toArray();
    }
}
