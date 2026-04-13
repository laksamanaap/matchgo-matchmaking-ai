<?php

namespace App\Notifications;

use App\Models\Team;
use Illuminate\Notifications\Notification;

class TeamVerificationRejected extends Notification
{
    public function __construct(
        private Team $team,
        private string $notes
    ) {}

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toDatabase(object $notifiable): array
    {
        return [
            'team_id'   => $this->team->id,
            'team_name' => $this->team->name,
            'notes'     => $this->notes,
            'message'   => "Verifikasi tim \"{$this->team->name}\" ditolak. Alasan: {$this->notes}",
        ];
    }
}
