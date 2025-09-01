<?php

namespace App\Observers;

use App\Models\Course;
use App\Models\User;

class CourseObserver
{
    /**
     * Handle the Course "created" event.
     */
    public function created(Course $course): void
    {
        $users = User::get();

        foreach ($users as $user) {
            $user->notify(new \App\Notifications\CourseCreatedNotification($course));
        }

        if ($course->youtube_embed) {
            $course->youtube_embed = $this->convertToEmbedUrl($course->youtube_embed);
            $course->save();
        }
    }

    /**
     * Handle the Course "updated" event.
     */
    public function updated(Course $course): void
    {
        //
        if ($course->youtube_embed) {
            $course->youtube_embed = $this->convertToEmbedUrl($course->youtube_embed);
            $course->saveQuietly();
        }
    }

    function convertToEmbedUrl($url)
    {
        // Controllo se l'URL è già in formato embed
        if (strpos($url, 'youtube.com/embed/') !== false) {
            return $url; // È già un URL embed, ritorna così com'è
        }

        // Pattern per estrarre l'ID del video da diversi formati YouTube
        $pattern = '/(?:youtube\.com\/(?:[^\/]+\/.+\/|(?:v|e(?:mbed)?)\/|.*[?&]v=)|youtu\.be\/)([^"&?\/\s]{11})/';

        if (preg_match($pattern, $url, $matches)) {
            return 'https://www.youtube.com/embed/' . $matches[1];
        }

        return $url; // Ritorna l'URL originale se non è un link YouTube valido
    }

    /**
     * Handle the Course "deleted" event.
     */
    public function deleted(Course $course): void
    {
        //
    }

    /**
     * Handle the Course "restored" event.
     */
    public function restored(Course $course): void
    {
        //
    }

    /**
     * Handle the Course "force deleted" event.
     */
    public function forceDeleted(Course $course): void
    {
        //
    }
}
