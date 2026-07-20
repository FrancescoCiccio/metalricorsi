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
            ->subject(__('Verifica il tuo Indirizzo Email'))
            ->line(__('Clicca sul pulsante qui sotto per verificare il tuo indirizzo email.'))
            ->action(__('Verifica Email'), $verificationUrl)
            ->line(__('Se non hai creato un account, non è necessaria alcuna azione.'));
    }
}
