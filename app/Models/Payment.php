<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Payment extends Model
{
    use HasFactory, HasUuids;

    protected $fillable = [
        'booking_id',
        'order_id',
        'status',
        'method',
        'gross_amount',
        'midtrans_transaction_id',
        'raw_notification',
    ];

    protected $casts = [
        'raw_notification' => 'array',
        'gross_amount' => 'integer',
    ];

    public function booking()
    {
        return $this->belongsTo(Booking::class);
    }
}
