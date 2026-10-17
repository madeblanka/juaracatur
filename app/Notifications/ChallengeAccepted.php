<?php

namespace App\Notifications;

use App\Models\Room;
use Illuminate\Notifications\Notification;

class ChallengeAccepted extends Notification
{
    public function __construct(private readonly Room $room)
    {
    }

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toDatabase(object $notifiable): array
    {
        return [
            'type' => 'challenge_accepted',
            'message' => "{$this->room->opponent->name} menerima tantanganmu (room {$this->room->code}).",
            'url' => route('rooms.show', $this->room),
        ];
    }
}
