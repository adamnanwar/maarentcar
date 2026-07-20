<?php

namespace App\Models;

use App\Models\Concerns\HasSlug;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Destination extends Model
{
    use HasFactory, SoftDeletes, HasSlug;

    protected $fillable = [
        'name',
        'slug',
        'category',
        'description',
        'address',
        'image_path',
        'addon_price',
        'is_active',
    ];

    protected $casts = [
        'addon_price' => 'decimal:2',
        'is_active' => 'boolean',
    ];

    public function tourPackages(): BelongsToMany
    {
        return $this->belongsToMany(TourPackage::class, 'package_destinations', 'destination_id', 'package_id')
            ->withPivot('sort_order');
    }

    public function bookingAddons(): HasMany
    {
        return $this->hasMany(BookingDestination::class);
    }
}
