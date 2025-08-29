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
    }

    /**
     * Handle the Course "updated" event.
     */
    public function updated(Course $course): void
    {
        //
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
