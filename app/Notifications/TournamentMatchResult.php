<?php

namespace App\Notifications;

use App\Models\TournamentMatch;
use Illuminate\Notifications\Notification;

class TournamentMatchResult extends Notification
{
    public function __construct(private readonly TournamentMatch $match)
    {
    }

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toDatabase(object $notifiable): array
    {
        $result = $this->match->winner_id === null
            ? 'seri'
            : ($this->match->winner_id === $notifiable->id ? 'kamu menang' : 'kamu kalah');

        return [
            'type' => 'tournament_match_result',
            'message' => "Hasil ronde {$this->match->round} di \"{$this->match->tournament->name}\": {$result}.",
            'url' => route('tournaments.show', $this->match->tournament),
        ];
    }
}
