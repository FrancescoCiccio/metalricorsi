<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Spatie\Tags\HasTags;

class Download extends Model
{
    use HasFactory;
    use HasTags;

    /**
     * 
     * The user witch a Download is setted
     * 
     */
    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class)->withTimestamps();
    }


    /**
     * 
     * The Groups witch a Download is setted
     * 
     */
    public function groups(): BelongsToMany
    {
        return $this->belongsToMany(Group::class)->withTimestamps();
    }
}
