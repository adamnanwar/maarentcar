<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BookingDestination extends Model
{
    public $timestamps = false;

    protected $fillable = ['booking_id', 'destination_id', 'price_at_booking'];

    protected $casts = [
        'price_at_booking' => 'decimal:2',
        'created_at' => 'datetime',
    ];

    public function booking(): BelongsTo
    {
        return $this->belongsTo(Booking::class);
    }

    public function destination(): BelongsTo
    {
        return $this->belongsTo(Destination::class);
    }
}
