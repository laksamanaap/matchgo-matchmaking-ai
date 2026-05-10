<?php

use App\Models\User;
use Illuminate\Support\Facades\Broadcast;

Broadcast::channel('App.Models.User.{id}', function ($user, $id) {
    return (int) $user->id === (int) $id;
});

Broadcast::channel('team.{teamId}', function (User $user, int $teamId) {
    return $user->ownedTeams()->where('id', $teamId)->exists()
        || $user->teamMemberships()->where('team_id', $teamId)->exists();
});
