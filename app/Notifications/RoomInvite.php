<?php

namespace App\Notifications;

use App\Models\Room;
use Illuminate\Notifications\Notification;

class RoomInvite extends Notification
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
            'type' => 'room_invite',
            'message' => "{$this->room->challenger->name} mengundangmu bertanding di room {$this->room->code} ({$this->room->wager_points} poin).",
            'url' => route('rooms.show', $this->room),
        ];
    }
}
