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

    protected $fillable = [
        'title',
        'cover_path',
        'description',
        'when',
        'online',
        'location',
        'max_attends',
        'order',
    ];

    protected $casts = [
        'when' => 'datetime',
        'additional_resources' => 'array',
        'relators' => 'array',
        'order' => 'integer',
    ];

    /**
     * The "booted" method of the model.
     */
    protected static function booted(): void
    {
        static::addGlobalScope('ordered', function ($query) {
            $query->orderBy('order', 'asc');
        });
    }

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
