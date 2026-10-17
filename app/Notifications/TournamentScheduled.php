<?php

namespace App\Notifications;

use App\Models\Tournament;
use Illuminate\Notifications\Notification;

class TournamentScheduled extends Notification
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
            'type' => 'tournament_scheduled',
            'message' => "Jadwal turnamen \"{$this->tournament->name}\" sudah dibuat. Cek jadwal pertandinganmu.",
            'url' => route('tournaments.show', $this->tournament),
        ];
    }
}
