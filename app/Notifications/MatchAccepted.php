<?php

namespace App\Notifications;

use App\Models\MatchRequest;
use Illuminate\Notifications\Notification;

class MatchAccepted extends Notification
{
    public function __construct(private MatchRequest $matchRequest) {}

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toDatabase(object $notifiable): array
    {
        return [
            'match_request_id'  => $this->matchRequest->id,
            'opponent_team'     => $this->matchRequest->opponentTeam->name,
            'preferred_date'    => $this->matchRequest->preferred_date,
            'message'           => "Tantangan kamu ke \"{$this->matchRequest->opponentTeam->name}\" diterima! Segera pilih lapangan.",
        ];
    }
}
