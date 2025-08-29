<?php

namespace App\Observers;

use App\Models\Download;

class DownloadObserver
{
    /**
     * Handle the Download "created" event.
     */
    public function created(Download $download): void
    {

        $user = $download->users;
        $groups = $download->groups;

        foreach ($groups as $group) {
            $users = $group->users;

            foreach ($users as $u) {
                $u->notify(new \App\Notifications\DownloadAviableNotification($download));
            }
        }

        foreach ($user as $u) {
            $u->notify(new \App\Notifications\DownloadAviableNotification($download));
        }
    }

    /**
     * Handle the Download "updated" event.
     */
    public function updated(Download $download): void
    {
        //
    }

    /**
     * Handle the Download "deleted" event.
     */
    public function deleted(Download $download): void
    {
        //
    }

    /**
     * Handle the Download "restored" event.
     */
    public function restored(Download $download): void
    {
        //
    }

    /**
     * Handle the Download "force deleted" event.
     */
    public function forceDeleted(Download $download): void
    {
        //
    }
}
