<?php

namespace App\Notifications;

use App\Models\Room;
use Illuminate\Notifications\Notification;

class RoomSettled extends Notification
{
    public function __construct(private readonly Room $room, private readonly bool $won, private readonly int $pointsChanged)
    {
    }

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toDatabase(object $notifiable): array
    {
        $opponent = $this->room->challenger_id === $notifiable->id ? $this->room->opponent : $this->room->challenger;

        $message = $this->won
            ? "Selamat, kamu menjadi pemenang! Dapat reward {$this->room->wager_points} poin dari {$opponent->name}."
            : "Terima kasih, kamu sangat sportif. Kekalahan adalah kemenangan yang tertunda, {$opponent->name}.";

        return [
            'type' => 'room_settled',
            'message' => $message,
            'url' => route('rooms.show', $this->room),
        ];
    }
}
