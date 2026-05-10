<?php

namespace App\Livewire;

use App\Events\MatchAcceptedEvent;
use App\Events\MatchRejectedEvent;
use App\Models\MatchmakingHistory;
use App\Models\MatchmakingMatch;
use App\Models\MatchmakingQueue as QueueModel;
use App\Models\MatchRequest;
use App\Models\Team;
use App\Services\AutoBookingService;
use App\Services\QueueManagerService;
use Illuminate\Support\Facades\DB;
use Livewire\Attributes\Computed;
use Livewire\Attributes\On;
use Livewire\Component;

class MatchmakingQueue extends Component
{
    public ?int $selectedTeamId = null;

    /** @var array<string, mixed>|null */
    public ?array $foundMatch = null;

    public ?string $errorMessage = null;
    public ?string $infoMessage  = null;

    public function mount(): void
    {
        $teams = $this->myTeams();
        if ($teams->count() === 1) {
            $this->selectedTeamId = $teams->first()->id;
        }
    }

    /** @return \Illuminate\Support\Collection<int, Team> */
    #[Computed]
    public function myTeams()
    {
        return auth()->user()->ownedTeams()
            ->where('verification_status', 'verified')
            ->get();
    }

    #[Computed]
    public function activeQueue(): ?QueueModel
    {
        if (! $this->selectedTeamId) {
            return null;
        }

        return QueueModel::where('team_id', $this->selectedTeamId)
            ->where('status', 'waiting')
            ->first();
    }

    #[Computed]
    public function queueState(): string
    {
        if ($this->foundMatch) {
            return 'match_found';
        }
        return $this->activeQueue ? 'queued' : 'idle';
    }

    #[Computed]
    public function status(): array
    {
        if (! $this->selectedTeamId) {
            return ['state' => 'idle'];
        }
        $team = Team::find($this->selectedTeamId);
        if (! $team) {
            return ['state' => 'idle'];
        }
        return app(QueueManagerService::class)->getStatus($team);
    }

    public function startMatchmaking(): void
    {
        $this->errorMessage = null;
        $this->infoMessage  = null;

        if (! $this->selectedTeamId) {
            $this->errorMessage = 'Pilih tim dulu.';
            return;
        }

        $team = Team::find($this->selectedTeamId);
        if (! $team || $team->owner_id !== auth()->id()) {
            $this->errorMessage = 'Hanya kapten yang bisa memulai matchmaking.';
            return;
        }

        if ($team->verification_status !== 'verified') {
            $this->errorMessage = 'Tim belum terverifikasi.';
            return;
        }

        app(QueueManagerService::class)->join($team, auth()->user());
        $this->infoMessage = 'Mencari lawan...';

        // Invalidate cached computed properties so the view re-fetches the queue.
        unset($this->activeQueue, $this->queueState, $this->status);
    }

    public function cancelMatchmaking(): void
    {
        $queue = $this->activeQueue;
        if (! $queue) {
            return;
        }
        app(QueueManagerService::class)->leave($queue);
        $this->foundMatch  = null;
        $this->infoMessage = 'Antrian dibatalkan.';

        unset($this->activeQueue, $this->queueState, $this->status);
    }

    public function acceptMatch(): void
    {
        if (! $this->foundMatch) {
            return;
        }

        $matchId = (int) $this->foundMatch['match_id'];
        $match = MatchmakingMatch::with(['queueA', 'queueB'])->find($matchId);

        if (! $match || $match->status !== 'pending') {
            $this->foundMatch = null;
            $this->errorMessage = 'Match sudah tidak aktif.';
            return;
        }

        if ($match->expires_at && $match->expires_at->isPast()) {
            $this->foundMatch = null;
            $this->errorMessage = 'Match sudah kedaluwarsa.';
            return;
        }

        $side   = $this->resolveSide($match);
        $teamId = $side === 'a' ? $match->queueA->team_id : $match->queueB->team_id;

        DB::transaction(function () use ($match, $side, $teamId) {
            $fresh = MatchmakingMatch::lockForUpdate()->find($match->id);

            if ($side === 'a') {
                $fresh->update(['accepted_by_a' => true]);
            } else {
                $fresh->update(['accepted_by_b' => true]);
            }

            $fresh->refresh();

            if ($fresh->isFullyAccepted()) {
                $teamA = $fresh->queueA->team;
                $teamB = $fresh->queueB->team;

                $matchRequest = MatchRequest::create([
                    'requester_team_id' => $teamA->id,
                    'opponent_team_id'  => $teamB->id,
                    'status'            => 'accepted',
                    'preferred_date'    => now()->addHours(AutoBookingService::MIN_LEAD_HOURS)->toDateString(),
                    'notes'             => 'Dibuat otomatis oleh sistem matchmaking.',
                ]);

                $fresh->update([
                    'status'           => 'accepted',
                    'match_request_id' => $matchRequest->id,
                ]);

                // Auto-book a venue + 2h slot starting now+2h.
                $futsalMatch = app(AutoBookingService::class)
                    ->autoBook($matchRequest, $teamA, $teamB);

                $this->logHistory($fresh, 'accepted');

                broadcast(new MatchAcceptedEvent($fresh->fresh(['queueA', 'queueB']), $teamA->id));
                broadcast(new MatchAcceptedEvent($fresh->fresh(['queueA', 'queueB']), $teamB->id));
            } else {
                broadcast(new MatchAcceptedEvent($fresh, $teamId));
            }
        });

        $match->refresh();

        if ($match->isFullyAccepted() && $match->match_request_id) {
            $this->foundMatch = null;

            $futsalMatch = \App\Models\FutsalMatch::with('venue')
                ->where('match_request_id', $match->match_request_id)
                ->first();

            if ($futsalMatch) {
                $venueName = $futsalMatch->venue?->name ?? 'lapangan';
                $when = \Carbon\Carbon::parse($futsalMatch->match_date)->translatedFormat('d M Y')
                      . ' ' . substr($futsalMatch->start_time, 0, 5);
                session()->flash('success', "Match berhasil! Lapangan dipesan otomatis di {$venueName} pada {$when}.");
            } else {
                session()->flash('error', 'Match diterima, tapi belum ada slot lapangan yang tersedia. Silakan booking manual.');
            }

            $this->redirect(route('match.index'), navigate: false);
            return;
        }

        $this->infoMessage = 'Menunggu konfirmasi tim lawan...';
    }

    public function rejectMatch(): void
    {
        if (! $this->foundMatch) {
            return;
        }

        $matchId = (int) $this->foundMatch['match_id'];
        $match = MatchmakingMatch::with(['queueA', 'queueB'])->find($matchId);

        if (! $match || $match->status !== 'pending') {
            $this->foundMatch = null;
            return;
        }

        $this->resolveSide($match);

        DB::transaction(function () use ($match) {
            $match->update(['status' => 'rejected']);
            $match->queueA?->update(['status' => 'cancelled']);
            $match->queueB?->update(['status' => 'cancelled']);
            $this->logHistory($match, 'rejected');
        });

        broadcast(new MatchRejectedEvent($match->fresh(['queueA', 'queueB']), 'rejected'));

        $this->foundMatch = null;
        $this->infoMessage = 'Match ditolak. Antrian dibatalkan.';
    }

    #[On('echo-private:team.{selectedTeamId},.match.found')]
    public function onMatchFound(array $payload): void
    {
        $this->foundMatch  = $payload;
        $this->infoMessage = 'Lawan ditemukan!';
    }

    #[On('echo-private:team.{selectedTeamId},.match.accepted')]
    public function onMatchAccepted(array $payload): void
    {
        if (! empty($payload['fully_accepted'])) {
            $this->foundMatch = null;
            session()->flash('success', 'Match berhasil! Lapangan dipesan otomatis.');
            $this->redirect(route('match.index'), navigate: false);
        }
    }

    #[On('echo-private:team.{selectedTeamId},.match.rejected')]
    public function onMatchRejected(array $payload): void
    {
        $this->foundMatch  = null;
        $this->infoMessage = $payload['reason'] === 'timeout'
            ? 'Lawan tidak merespons tepat waktu.'
            : 'Lawan menolak match.';
    }

    #[On('echo-private:team.{selectedTeamId},.queue.timeout')]
    public function onQueueTimeout(array $payload): void
    {
        $this->foundMatch  = null;
        $this->errorMessage = $payload['message'] ?? 'Antrian kedaluwarsa.';
    }

    private function resolveSide(MatchmakingMatch $match): string
    {
        $userId = auth()->id();
        if ($match->queueA?->captain_id === $userId) {
            return 'a';
        }
        if ($match->queueB?->captain_id === $userId) {
            return 'b';
        }
        abort(403, 'Bukan kapten tim yang terlibat di match ini.');
    }

    private function logHistory(MatchmakingMatch $match, string $result): void
    {
        $teamA = $match->queueA;
        $teamB = $match->queueB;

        if ($teamA) {
            MatchmakingHistory::create([
                'team_id'                => $teamA->team_id,
                'matched_team_id'        => $teamB?->team_id,
                'compatibility_score'    => $match->compatibility_score,
                'queue_duration_seconds' => $teamA->queued_at?->diffInSeconds(now()) ?? 0,
                'result'                 => $result,
            ]);
        }

        if ($teamB) {
            MatchmakingHistory::create([
                'team_id'                => $teamB->team_id,
                'matched_team_id'        => $teamA?->team_id,
                'compatibility_score'    => $match->compatibility_score,
                'queue_duration_seconds' => $teamB->queued_at?->diffInSeconds(now()) ?? 0,
                'result'                 => $result,
            ]);
        }
    }

    public function render()
    {
        return view('livewire.matchmaking-queue');
    }
}
