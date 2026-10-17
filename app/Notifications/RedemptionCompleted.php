<?php

namespace App\Notifications;

use App\Models\Redemption;
use Illuminate\Notifications\Notification;

class RedemptionCompleted extends Notification
{
    public function __construct(private readonly Redemption $redemption)
    {
    }

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toDatabase(object $notifiable): array
    {
        return [
            'type' => 'redemption_completed',
            'message' => "Barang \"{$this->redemption->item_name}\" hasil penukaranmu sudah diproses vendor.",
            'url' => route('exchange.index'),
        ];
    }
}
