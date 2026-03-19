<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TrackUrl extends Model
{
    protected $table = 'urls';

    protected $fillable = [
        'website',
        'url',
    ];

    public function track(): BelongsTo
    {
        return $this->belongsTo(Track::class);
    }
}
