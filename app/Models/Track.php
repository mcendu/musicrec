<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Track extends Model
{
    protected $fillable = [
        'title',
        'tags',
    ];

    protected $casts = [
        'tags' => 'array',
    ];

    public function artist(): BelongsTo
    {
        return $this->belongsTo(Artist::class);
    }

    public function urls(): HasMany
    {
        return $this->hasMany(TrackUrl::class);
    }
}
