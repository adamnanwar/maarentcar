<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Payment extends Model
{
    use HasFactory;

    const STATUS_MENUNGGU = 'menunggu';
    const STATUS_TERVERIFIKASI = 'terverifikasi';
    const STATUS_DITOLAK = 'ditolak';

    protected $fillable = [
        'booking_id',
        'amount',
        'bank_sender_name',
        'bank_sender_account',
        'proof_path',
        'status',
        'paid_at',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'paid_at' => 'datetime',
    ];

    public function booking(): BelongsTo
    {
        return $this->belongsTo(Booking::class);
    }

    public function verifications(): HasMany
    {
        return $this->hasMany(PaymentVerification::class);
    }
}
