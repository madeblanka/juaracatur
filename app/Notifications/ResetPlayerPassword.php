<?php

namespace App\Notifications;

use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Notifications\Messages\MailMessage;

class ResetPlayerPassword extends ResetPassword
{
    public function toMail($notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('Atur ulang password akunmu di Juaracatur')
            ->greeting('Halo, '.$notifiable->name.'!')
            ->line('Kami menerima permintaan untuk mengatur ulang password akunmu.')
            ->action('Atur Ulang Password', $this->resetUrl($notifiable))
            ->line('Link ini berlaku selama '.config('auth.passwords.'.config('auth.defaults.passwords').'.expire').' menit.')
            ->line('Jika kamu tidak meminta reset password, abaikan email ini. Password kamu tidak akan berubah.');
    }
}
