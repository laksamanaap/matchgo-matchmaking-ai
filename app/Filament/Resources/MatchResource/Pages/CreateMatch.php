<?php

namespace App\Filament\Resources\MatchResource\Pages;

use App\Filament\Resources\MatchResource;
use App\Models\Booking;
use App\Services\MatchCostService;
use Carbon\Carbon;
use Filament\Resources\Pages\CreateRecord;

class CreateMatch extends CreateRecord
{
    protected static string $resource = MatchResource::class;

    protected function afterCreate(): void
    {
        $this->syncBookingAndCost();
    }

    private function syncBookingAndCost(): void
    {
        $match = $this->record;

        if (! $match->field_id || ! $match->match_date || ! $match->start_time) {
            return;
        }

        Booking::updateOrCreate(
            ['match_id' => $match->id],
            [
                'field_id' => $match->field_id,
                'start_at' => Carbon::parse($match->match_date->toDateString() . ' ' . $match->start_time),
                'duration_hours' => (int) ceil($match->duration_minutes / 60),
                'status' => $match->status === 'cancelled' ? 'cancelled' : 'confirmed',
            ]
        );

        app(MatchCostService::class)->calculate($match->refresh());
    }
}
