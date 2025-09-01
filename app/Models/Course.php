<?php

namespace App\Models;

use Spatie\Tags\HasTags;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Course extends Model
{

    use HasFactory;
    use HasTags;

    protected $casts = [
        'when' => 'datetime',
        'additional_resources' => 'array',
        'relators' => 'array',
    ];

    /**
     * 
     * The App\Model\User that has joined the course
     * 
     */
    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class);
    }
}
