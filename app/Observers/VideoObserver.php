<?php

namespace App\Observers;

use App\Models\Video;

class VideoObserver
{
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
     * Handle the Video "created" event.
     */
    public function created(Video $video): void
    {
        if ($video->video_path) {
            $video->video_path = $this->convertToEmbedUrl($video->video_path);
            $video->save();
        }
    }

    /**
     * Handle the Video "updated" event.
     */
    public function updated(Video $video): void
    {
        if ($video->video_path) {
            $video->video_path = $this->convertToEmbedUrl($video->video_path);
            $video->saveQuietly();
        }
    }

    /**
     * Handle the Video "deleted" event.
     */
    public function deleted(Video $video): void
    {
        //
    }

    /**
     * Handle the Video "restored" event.
     */
    public function restored(Video $video): void
    {
        //
    }

    /**
     * Handle the Video "force deleted" event.
     */
    public function forceDeleted(Video $video): void
    {
        //
    }
}
