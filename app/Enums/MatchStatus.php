<?php

namespace App\Enums;

enum MatchStatus: string
{
    case Scheduled = 'scheduled';
    case Ongoing   = 'ongoing';
    case Completed = 'completed';
    case Cancelled = 'cancelled';

    public function label(): string
    {
        return match($this) {
            self::Scheduled => 'Dijadwalkan',
            self::Ongoing   => 'Berlangsung',
            self::Completed => 'Selesai',
            self::Cancelled => 'Dibatalkan',
        };
    }

    public function color(): string
    {
        return match($this) {
            self::Scheduled => 'info',
            self::Ongoing   => 'warning',
            self::Completed => 'success',
            self::Cancelled => 'danger',
        };
    }

    public static function options(): array
    {
        return collect(self::cases())
            ->mapWithKeys(fn ($case) => [$case->value => $case->label()])
            ->toArray();
    }
}
