<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Tracker extends Model
{
    protected $fillable = [
        'imei',
        'phone',
        'balance',
        'power',
        'is_charging',
        'car_id',
        'person_id',
        'responsible_id'
    ];

    public function person(): HasOne
    {
        return $this->hasOne(Person::class, 'id', 'person_id');
    }

    public function car(): HasOne
    {
        return $this->hasOne(Car::class, 'id', 'car_id');
    }

    public function position(): HasMany
    {
        return $this->hasMany(Position::class, 'tracker_id', 'id')->latest();
    }

    public function positions(): HasMany
    {
        return $this->hasMany(Position::class, 'tracker_id', 'id')->orderByDesc('id');
    }
}
