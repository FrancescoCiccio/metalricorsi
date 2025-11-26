<?php

namespace App\Notifications;

use Illuminate\Auth\Notifications\VerifyEmail as BaseVerifyEmail;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;

class VerifyEmailNotification extends BaseVerifyEmail implements ShouldQueue
{
    use Queueable;

    /**
     * Get the mail representation of the notification.
     */
    public function toMail($notifiable): MailMessage
    {
        $verificationUrl = $this->verificationUrl($notifiable);

        return (new MailMessage)
            ->subject('Verifica il tuo Indirizzo Email')
            ->line('Clicca sul pulsante qui sotto per verificare il tuo indirizzo email.')
            ->action('Verifica Email', $verificationUrl)
            ->line('Se non hai creato un account, non è necessaria alcuna azione.');
    }
}
