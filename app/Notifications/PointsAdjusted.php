<?php

namespace App\Notifications;

use Illuminate\Notifications\Notification;

class PointsAdjusted extends Notification
{
    public function __construct(private readonly int $amount, private readonly ?string $description)
    {
    }

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toDatabase(object $notifiable): array
    {
        $sign = $this->amount > 0 ? '+' : '';
        $keterangan = $this->description ? " Keterangan: {$this->description}." : '';

        return [
            'type' => 'points_adjusted',
            'message' => "Admin menyesuaikan poinmu sebesar {$sign}{$this->amount}.{$keterangan}",
            'url' => route('points.history'),
        ];
    }
}
