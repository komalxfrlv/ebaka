<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Car extends Model
{
    protected $fillable = [
        'mark', 'model', 'reg_number', 'vin'
    ];
}
