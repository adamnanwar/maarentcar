<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Booking extends Model
{
    use HasFactory, HasUuids;

    // Status constants
    const STATUS_PENDING_VERIFICATION = 'PENDING_VERIFICATION';
    const STATUS_PENDING_PAYMENT = 'PENDING_PAYMENT';
    const STATUS_PAID = 'PAID';
    const STATUS_IN_PROGRESS = 'IN_PROGRESS';
    const STATUS_COMPLETED = 'COMPLETED';
    const STATUS_CANCELLED = 'CANCELLED';
    const STATUS_REJECTED = 'REJECTED';

    protected $fillable = [
        'order_id',
        'user_id',
        'car_id',
        'tour_package_id',
        'start_date',
        'end_date',
        'pickup_location',
        'use_driver',
        'notes',
        'ktp_image_url',
        'sim_image_url',
        'admin_note',
        'total_price',
        'status',
        'snap_token',
        'payment_deadline',
    ];

    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
        'use_driver' => 'boolean',
        'payment_deadline' => 'datetime',
        'total_price' => 'integer',
    ];

    protected $appends = ['duration_days', 'status_label'];

    public function getDurationDaysAttribute(): int
    {
        if (!$this->start_date || !$this->end_date) {
            return 0;
        }
        return $this->start_date->diffInDays($this->end_date) + 1;
    }

    public function getStatusLabelAttribute(): string
    {
        return match ($this->status) {
            self::STATUS_PENDING_VERIFICATION => 'Menunggu Verifikasi',
            self::STATUS_PENDING_PAYMENT => 'Menunggu Pembayaran',
            self::STATUS_PAID => 'Sudah Dibayar',
            self::STATUS_IN_PROGRESS => 'Sedang Berjalan',
            self::STATUS_COMPLETED => 'Selesai',
            self::STATUS_CANCELLED => 'Dibatalkan',
            self::STATUS_REJECTED => 'Ditolak',
            default => $this->status,
        };
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function car(): BelongsTo
    {
        return $this->belongsTo(Car::class);
    }

    public function tourPackage(): BelongsTo
    {
        return $this->belongsTo(TourPackage::class);
    }

    public function payment(): HasOne
    {
        return $this->hasOne(Payment::class);
    }

    public function isPendingVerification(): bool
    {
        return $this->status === self::STATUS_PENDING_VERIFICATION;
    }

    public function isPendingPayment(): bool
    {
        return $this->status === self::STATUS_PENDING_PAYMENT;
    }

    public function canBePaid(): bool
    {
        return $this->status === self::STATUS_PENDING_PAYMENT;
    }

    public function canBeCancelled(): bool
    {
        return in_array($this->status, [
            self::STATUS_PENDING_VERIFICATION,
            self::STATUS_PENDING_PAYMENT,
        ]);
    }
}
