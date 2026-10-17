<?php

namespace App\Notifications;

use App\Models\Room;
use App\Models\User;
use Illuminate\Notifications\Notification;

class ResultReported extends Notification
{
    public function __construct(private readonly Room $room, private readonly User $reporter)
    {
    }

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toDatabase(object $notifiable): array
    {
        return [
            'type' => 'result_reported',
            'message' => "{$this->reporter->name} melaporkan hasil room {$this->room->code}. Konfirmasi hasilmu.",
            'url' => route('rooms.show', $this->room),
        ];
    }
}
