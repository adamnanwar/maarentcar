<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Car extends Model
{
    use HasFactory, HasUuids;

    protected $fillable = [
        'name',
        'brand',
        'type',
        'transmission',
        'seats',
        'price_per_day',
        'with_driver_price_per_day',
        'is_active',
        'thumbnail_url',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'seats' => 'integer',
        'price_per_day' => 'integer',
        'with_driver_price_per_day' => 'integer',
    ];
}
