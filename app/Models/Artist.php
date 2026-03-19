<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Artist extends Model
{
    protected $fillable = [
        'name',
    ];

    public function tracks(): HasMany
    {
        return $this->hasMany(Track::class);
    }
}
