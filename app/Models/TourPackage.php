<?php

namespace App\Models;

use App\Models\Concerns\HasSlug;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class TourPackage extends Model
{
    use HasFactory, SoftDeletes, HasSlug;

    protected $fillable = [
        'vehicle_id',
        'name',
        'slug',
        'seat_capacity',
        'duration_days',
        'driver_included',
        'price',
        'description',
        'image_path',
        'is_active',
    ];

    protected $casts = [
        'seat_capacity' => 'integer',
        'duration_days' => 'integer',
        'driver_included' => 'boolean',
        'price' => 'decimal:2',
        'is_active' => 'boolean',
    ];

    public function vehicle(): BelongsTo
    {
        return $this->belongsTo(Vehicle::class);
    }

    public function destinations(): BelongsToMany
    {
        return $this->belongsToMany(Destination::class, 'package_destinations', 'package_id', 'destination_id')
            ->withPivot('sort_order')
            ->orderBy('package_destinations.sort_order');
    }

    public function bookings(): HasMany
    {
        return $this->hasMany(Booking::class, 'package_id');
    }

    public function reviews(): HasMany
    {
        return $this->hasMany(Review::class, 'package_id');
    }
}
