<?php

namespace App\Enums;

enum VerificationStatus: string
{
    case Unverified = 'unverified';
    case Pending    = 'pending';
    case Verified   = 'verified';
    case Rejected   = 'rejected';

    public function label(): string
    {
        return match($this) {
            self::Unverified => 'Belum Diverifikasi',
            self::Pending    => 'Menunggu Verifikasi',
            self::Verified   => 'Terverifikasi',
            self::Rejected   => 'Ditolak',
        };
    }

    public function color(): string
    {
        return match($this) {
            self::Unverified => 'gray',
            self::Pending    => 'warning',
            self::Verified   => 'success',
            self::Rejected   => 'danger',
        };
    }

    public static function options(): array
    {
        return collect(self::cases())
            ->mapWithKeys(fn ($case) => [$case->value => $case->label()])
            ->toArray();
    }
}
