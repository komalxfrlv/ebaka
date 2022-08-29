<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Person extends Model
{
    protected $fillable = [
        'name', 'surname', 'phone', 'is_responsible'
    ];

    public function trackers(): HasMany
    {
        return $this->hasMany(Tracker::class, 'responsible_id', 'id');
    }
}
