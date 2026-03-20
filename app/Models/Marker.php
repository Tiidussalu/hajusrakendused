<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Casts\Attribute;

class Marker extends Model
{
    use HasFactory;

    public $timestamps = false;

    protected $fillable = [
        'name',
        'latitude',
        'longitude',
        'description',
        'added',
        'edited',
    ];

    protected $casts = [
        'latitude'  => 'float',
        'longitude' => 'float',
        'added'     => 'datetime',
        'edited'    => 'datetime',
    ];
}
