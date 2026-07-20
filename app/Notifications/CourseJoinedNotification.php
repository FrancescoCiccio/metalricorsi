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
            ->subject(__('Iscrizione Confermata - :title', ['title' => $this->course->title]))
            ->greeting(__('Ciao :name!', ['name' => $this->user->name]))
            ->line(__('Ti sei iscritto al corso: :title', ['title' => $this->course->title]))
            ->line(__('Si terrà il giorno: :date', ['date' => $this->course->when->format('d/m/Y H:i')]))
            ->action(__('Accedi al webinar'), url($this->course->webinar_url))
            ->line(__('Grazie per esserti iscritto!'))
            ->line(__('Nel caso in cui fosse richiesto'))
            ->line(__('La password per accedere al webinar è: :password', ['password' => $this->course->webinar_password ?? __('Non specificata')]))
            ->line(__('L\'ID del webinar è: :id', ['id' => $this->course->webinar_id ?? __('Non specificato')]))
            ->salutation(__('Cordiali saluti, Il Team'));

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
