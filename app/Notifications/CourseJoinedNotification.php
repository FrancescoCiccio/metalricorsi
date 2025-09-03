<?php

namespace App\Notifications;

use App\Models\User;
use App\Models\Course;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;

class CourseJoinedNotification extends Notification implements ShouldQueue
{
    use Queueable;

    /**
     * Create a new notification instance.
     */
    public function __construct(public Course $course, public User $user)
    {
        //
    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('Iscrizione Confermata - ' . $this->course->title) // Oggetto personalizzato
            ->greeting("Ciao {$this->user->name}!") // Saluto personalizzato
            ->line("Ti sei iscritto al corso: {$this->course->title}")
            ->line("Si terrà il giorno: {$this->course->when->format('d/m/Y H:i')}")
            ->action('Accedi al webinar', url($this->course->webinar_url))
            ->line('Grazie per esserti iscritto!')
            ->line('Nel caso in cui fosse richiesto')
            ->line("la password per accedere al webinar è: " . $this->course->webinar_password ?? 'Non specificata')
            ->line('L\'ID del webinar è: ' . ($this->course->webinar_id ?? 'Non specificato'))
            ->salutation('Cordiali saluti, Il Team'); // Saluto finale personalizzato

    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            //
        ];
    }
}
