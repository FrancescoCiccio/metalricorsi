<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Group extends Model
{
    use HasFactory;

    /**
     * 
     * The User that the group contains
     * 
     */
    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class)->withTimestamps();
    }


    /**
     * 
     * The Download associated with that type of group
     * 
     */
    public function downloads()
    {
        return $this->belongsToMany(Download::class)->withTimestamps();
    }
}
