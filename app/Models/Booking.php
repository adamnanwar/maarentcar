<?php

namespace App\Models;

use App\Notifications\BookingStatusChangedNotification;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Booking extends Model
{
    use HasFactory;

    const TYPE_MOBIL = 'mobil';
    const TYPE_PAKET_WISATA = 'paket_wisata';

    const STATUS_MENUNGGU_PEMBAYARAN = 'menunggu_pembayaran';
    const STATUS_MENUNGGU_VERIFIKASI = 'menunggu_verifikasi';
    const STATUS_DIKONFIRMASI = 'dikonfirmasi';
    const STATUS_BERLANGSUNG = 'berlangsung';
    const STATUS_SELESAI = 'selesai';
    const STATUS_DITOLAK = 'ditolak';
    const STATUS_DIBATALKAN = 'dibatalkan';

    const DELIVERY_PICKUP_AT_OFFICE = 'pickup_at_office';
    const DELIVERY_DELIVERED_TO_ADDRESS = 'delivered_to_address';
    const DELIVERY_DRIVER_PICKUP = 'driver_pickup';

    protected $fillable = [
        'booking_code',
        'user_id',
        'booking_type',
        'vehicle_id',
        'package_id',
        'start_datetime',
        'end_datetime',
        'duration_days',
        'with_driver',
        'delivery_method',
        'pickup_address_snapshot',
        'passenger_count',
        'base_price',
        'driver_fee',
        'delivery_fee',
        'addon_total',
        'discount',
        'total_price',
        'status',
        'notes',
        'internal_notes',
    ];

    protected $casts = [
        'start_datetime' => 'datetime',
        'end_datetime' => 'datetime',
        'duration_days' => 'integer',
        'with_driver' => 'boolean',
        'pickup_address_snapshot' => 'array',
        'passenger_count' => 'integer',
        'base_price' => 'decimal:2',
        'driver_fee' => 'decimal:2',
        'delivery_fee' => 'decimal:2',
        'addon_total' => 'decimal:2',
        'discount' => 'decimal:2',
        'total_price' => 'decimal:2',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function vehicle(): BelongsTo
    {
        return $this->belongsTo(Vehicle::class);
    }

    public function package(): BelongsTo
    {
        return $this->belongsTo(TourPackage::class, 'package_id');
    }

    public function destinations(): HasMany
    {
        return $this->hasMany(BookingDestination::class);
    }

    public function statusLogs(): HasMany
    {
        return $this->hasMany(BookingStatusLog::class);
    }

    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class);
    }

    public function latestPayment(): HasOne
    {
        return $this->hasOne(Payment::class)->latestOfMany();
    }

    public function review(): HasOne
    {
        return $this->hasOne(Review::class);
    }

    public function statusLabel(): string
    {
        return match ($this->status) {
            self::STATUS_MENUNGGU_PEMBAYARAN => 'Menunggu Pembayaran',
            self::STATUS_MENUNGGU_VERIFIKASI => 'Menunggu Verifikasi',
            self::STATUS_DIKONFIRMASI => 'Dikonfirmasi',
            self::STATUS_BERLANGSUNG => 'Sedang Berlangsung',
            self::STATUS_SELESAI => 'Selesai',
            self::STATUS_DITOLAK => 'Ditolak',
            self::STATUS_DIBATALKAN => 'Dibatalkan',
            default => $this->status,
        };
    }

    public function canBeCancelledByCustomer(): bool
    {
        return in_array($this->status, [
            self::STATUS_MENUNGGU_PEMBAYARAN,
            self::STATUS_MENUNGGU_VERIFIKASI,
        ]);
    }

    public function canBeReviewedBy(User $user): bool
    {
        return $this->user_id === $user->id
            && $this->status === self::STATUS_SELESAI
            && ! $this->review;
    }

    public function transitionTo(string $status, ?User $changedBy = null, ?string $note = null, ?string $notificationMessage = null): void
    {
        $from = $this->status;

        $this->update(['status' => $status]);

        $this->statusLogs()->create([
            'from_status' => $from,
            'to_status' => $status,
            'changed_by' => $changedBy?->id,
            'note' => $note,
        ]);

        $this->user->notify(new BookingStatusChangedNotification($this, $notificationMessage));
    }
}
