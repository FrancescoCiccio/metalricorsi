<?php

namespace App\Models;

use Spatie\Tags\HasTags;
use Illuminate\Database\Eloquent\Model;

class Video extends Model
{
    use HasTags;

    protected $fillable = [
        'title',
        'description',
        'video_path',
        'order',
    ];

    protected $casts = [
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
}
