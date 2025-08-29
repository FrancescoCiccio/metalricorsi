<?php

namespace App\Observers;

use App\Models\Group;
use App\Models\User;
use App\Notifications\UserCreatedNotification;

class UserObserver
{
    /**
     * Handle the User "created" event.
     */
    public function created(User $user): void
    {
        $defaultGroups = Group::where('is_default', true)->get();

        foreach ($defaultGroups as $group) {
            $user->groups()->attach($group->id);
        }

        $admin = User::role('super_admin')->get();

        foreach ($admin as $adminUser) {
            $user->notify(new UserCreatedNotification($user, $adminUser));
        }
    }

    /**
     * Handle the User "updated" event.
     */
    public function updated(User $user): void
    {
        //
    }

    /**
     * Handle the User "deleted" event.
     */
    public function deleted(User $user): void
    {
        //
    }

    /**
     * Handle the User "restored" event.
     */
    public function restored(User $user): void
    {
        //
    }

    /**
     * Handle the User "force deleted" event.
     */
    public function forceDeleted(User $user): void
    {
        //
    }
}
