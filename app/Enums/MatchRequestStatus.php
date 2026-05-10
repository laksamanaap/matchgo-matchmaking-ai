<?php

namespace App\Enums;

enum MatchRequestStatus: string
{
    case Pending   = 'pending';
    case Accepted  = 'accepted';
    case Rejected  = 'rejected';
    case Cancelled = 'cancelled';

    public function label(): string
    {
        return match($this) {
            self::Pending   => 'Menunggu',
            self::Accepted  => 'Diterima',
            self::Rejected  => 'Ditolak',
            self::Cancelled => 'Dibatalkan',
        };
    }

    public function color(): string
    {
        return match($this) {
            self::Pending   => 'warning',
            self::Accepted  => 'success',
            self::Rejected  => 'danger',
            self::Cancelled => 'gray',
        };
    }

    public static function options(): array
    {
        return collect(self::cases())
            ->mapWithKeys(fn ($case) => [$case->value => $case->label()])
            ->toArray();
    }
}
