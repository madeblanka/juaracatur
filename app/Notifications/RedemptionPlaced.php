<?php

namespace App\Notifications;

use App\Models\Redemption;
use Illuminate\Notifications\Notification;

class RedemptionPlaced extends Notification
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
            'type' => 'redemption_placed',
            'message' => "{$this->redemption->user->name} menukar poin dengan barang \"{$this->redemption->item_name}\". Proses penukaran di menu Vendor.",
            'url' => route('vendor.redemptions.index'),
        ];
    }
}
