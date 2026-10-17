<?php

namespace App\Notifications;

use App\Models\Tournament;
use Illuminate\Notifications\Notification;

class TournamentMatchReport extends Notification
{
    public function __construct(
        private readonly Tournament $tournament,
        private readonly string $message,
    ) {
    }

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toDatabase(object $notifiable): array
    {
        return [
            'type' => 'tournament_match_report',
            'message' => $this->message,
            'url' => route('tournaments.show', $this->tournament),
        ];
    }
}
