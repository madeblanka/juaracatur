<?php

namespace App\Notifications;

use Illuminate\Auth\Notifications\VerifyEmail;
use Illuminate\Notifications\Messages\MailMessage;

class VerifyPlayerEmail extends VerifyEmail
{
    public function toMail($notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('Verifikasi email akunmu di Juaracatur')
            ->greeting('Halo, '.$notifiable->name.'!')
            ->line('Terima kasih sudah mendaftar di Juaracatur. Klik tombol di bawah untuk memverifikasi email kamu.')
            ->line('Akun yang sudah terverifikasi bisa menantang pemain lain, menerima undangan, dan bertanding.')
            ->action('Verifikasi Email', $this->verificationUrl($notifiable))
            ->line('Jika kamu tidak merasa mendaftar di Juaracatur, abaikan email ini.');
    }
}
