<?php

namespace App\Models;

use App\Models\Concerns\HasSlug;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Vehicle extends Model
{
    use HasFactory, SoftDeletes, HasSlug;

    const STATUS_TERSEDIA = 'tersedia';
    const STATUS_PERAWATAN = 'perawatan';
    const STATUS_NONAKTIF = 'nonaktif';

    const TRANSMISSION_MANUAL = 'manual';
    const TRANSMISSION_AUTOMATIC = 'automatic';

    protected $fillable = [
        'category_id',
        'name',
        'slug',
        'brand',
        'model',
        'year',
        'plate_number',
        'transmission',
        'fuel_type',
        'seat_capacity',
        'price_per_day',
        'driver_fee_per_day',
        'base_delivery_fee',
        'description',
        'status',
        'is_active',
    ];

    protected $casts = [
        'year' => 'integer',
        'seat_capacity' => 'integer',
        'price_per_day' => 'decimal:2',
        'driver_fee_per_day' => 'decimal:2',
        'base_delivery_fee' => 'decimal:2',
        'is_active' => 'boolean',
    ];

    public function category(): BelongsTo
    {
        return $this->belongsTo(VehicleCategory::class, 'category_id');
    }

    public function images(): HasMany
    {
        return $this->hasMany(VehicleImage::class)->orderBy('sort_order');
    }

    public function primaryImage(): HasMany
    {
        return $this->images()->where('is_primary', true);
    }

    public function bookings(): HasMany
    {
        return $this->hasMany(Booking::class);
    }

    public function tourPackages(): HasMany
    {
        return $this->hasMany(TourPackage::class);
    }

    public function reviews(): HasMany
    {
        return $this->hasMany(Review::class);
    }

    public function isAvailableFor(\DateTimeInterface $start, \DateTimeInterface $end, ?int $excludeBookingId = null): bool
    {
        $query = $this->bookings()
            ->whereIn('status', ['menunggu_verifikasi', 'dikonfirmasi', 'berlangsung'])
            ->where(function ($q) use ($start, $end) {
                $q->whereBetween('start_datetime', [$start, $end])
                    ->orWhereBetween('end_datetime', [$start, $end])
                    ->orWhere(function ($q2) use ($start, $end) {
                        $q2->where('start_datetime', '<=', $start)->where('end_datetime', '>=', $end);
                    });
            });

        if ($excludeBookingId) {
            $query->where('id', '!=', $excludeBookingId);
        }

        return ! $query->exists();
    }
}
