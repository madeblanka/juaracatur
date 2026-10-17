<?php

namespace App\Notifications;

use App\Models\Tournament;
use Illuminate\Notifications\Notification;

class TournamentInvited extends Notification
{
    public function __construct(private readonly Tournament $tournament)
    {
    }

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toDatabase(object $notifiable): array
    {
        return [
            'type' => 'tournament_invited',
            'message' => "Kamu diundang mengikuti turnamen {$this->tournament->name}.",
            'url' => route('tournaments.show', $this->tournament),
        ];
    }
}
