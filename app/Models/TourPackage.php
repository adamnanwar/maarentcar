<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class TourPackage extends Model
{
    use HasFactory, HasUuids;

    protected $fillable = [
        'name',
        'price',
        'duration_days',
        'description',
        'thumbnail_url',
        'is_active',
    ];

    protected $casts = [
        'price' => 'integer',
        'duration_days' => 'integer',
        'is_active' => 'boolean',
    ];

    public function itineraries(): HasMany
    {
        return $this->hasMany(TourItinerary::class)->orderBy('day_number')->orderBy('time');
    }

    public function bookings(): HasMany
    {
        return $this->hasMany(Booking::class);
    }
}
