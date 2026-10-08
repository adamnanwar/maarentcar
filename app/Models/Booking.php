<?php

namespace App\Models;

use App\Notifications\BookingRentalReminderNotification;
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
        'ktp_photo_path',
        'passenger_count',
        'base_price',
        'driver_fee',
        'delivery_fee',
        'addon_total',
        'discount',
        'total_price',
        'status',
        'payment_due_at',
        'notified_start_at',
        'notified_ending_soon_at',
        'notified_return_reminder_at',
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
        'payment_due_at' => 'datetime',
        'notified_start_at' => 'datetime',
        'notified_ending_soon_at' => 'datetime',
        'notified_return_reminder_at' => 'datetime',
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

    /**
     * Status yang dianggap "masih aktif" untuk keperluan pembatasan
     * (mis. 1 booking mobil aktif per pelanggan).
     */
    public static function activeStatuses(): array
    {
        return [
            self::STATUS_MENUNGGU_PEMBAYARAN,
            self::STATUS_MENUNGGU_VERIFIKASI,
            self::STATUS_DIKONFIRMASI,
            self::STATUS_BERLANGSUNG,
        ];
    }

    public function isPaymentOverdue(): bool
    {
        return $this->status === self::STATUS_MENUNGGU_PEMBAYARAN
            && $this->payment_due_at !== null
            && $this->payment_due_at->isPast();
    }

    /**
     * Batalkan booking ini secara otomatis apabila batas waktu 1 jam
     * pembayaran sudah lewat dan pelanggan belum mengunggah bukti transfer.
     */
    public function expireIfOverdue(): bool
    {
        if (! $this->isPaymentOverdue()) {
            return false;
        }

        $this->transitionTo(
            self::STATUS_DIBATALKAN,
            null,
            'Dibatalkan otomatis oleh sistem karena batas waktu pembayaran 1 jam telah lewat.',
            'Pesanan '.$this->booking_code.' dibatalkan otomatis karena tidak ada pembayaran dalam waktu 1 jam.'
        );

        return true;
    }

    /**
     * Sapu seluruh booking yang masih "menunggu pembayaran" namun sudah
     * melewati batas waktu 1 jam, lalu batalkan secara otomatis.
     */
    public static function sweepOverduePayments(): int
    {
        $expired = static::query()
            ->where('status', self::STATUS_MENUNGGU_PEMBAYARAN)
            ->whereNotNull('payment_due_at')
            ->where('payment_due_at', '<', now())
            ->get();

        foreach ($expired as $booking) {
            $booking->expireIfOverdue();
        }

        return $expired->count();
    }

    /**
     * Kirim notifikasi masa sewa: dimulai hari ini, akan segera berakhir,
     * dan pengingat mengembalikan mobil apabila sudah lewat waktu tapi
     * belum ditandai selesai oleh admin. Aman dipanggil berkali-kali
     * karena masing-masing jenis hanya dikirim sekali per booking.
     */
    public static function sweepRentalReminders(): int
    {
        $sent = 0;

        static::query()
            ->whereIn('status', [self::STATUS_DIKONFIRMASI, self::STATUS_BERLANGSUNG])
            ->whereNull('notified_start_at')
            ->where('start_datetime', '<=', now())
            ->get()
            ->each(function (self $booking) use (&$sent) {
                $booking->user->notify(new BookingRentalReminderNotification($booking, BookingRentalReminderNotification::REMINDER_START));
                $booking->update(['notified_start_at' => now()]);
                $sent++;
            });

        static::query()
            ->where('status', self::STATUS_BERLANGSUNG)
            ->whereNull('notified_ending_soon_at')
            ->whereBetween('end_datetime', [now(), now()->addHours(3)])
            ->get()
            ->each(function (self $booking) use (&$sent) {
                $booking->user->notify(new BookingRentalReminderNotification($booking, BookingRentalReminderNotification::REMINDER_ENDING_SOON));
                $booking->update(['notified_ending_soon_at' => now()]);
                $sent++;
            });

        static::query()
            ->where('status', self::STATUS_BERLANGSUNG)
            ->whereNull('notified_return_reminder_at')
            ->where('end_datetime', '<', now())
            ->get()
            ->each(function (self $booking) use (&$sent) {
                $booking->user->notify(new BookingRentalReminderNotification($booking, BookingRentalReminderNotification::REMINDER_RETURN));
                $booking->update(['notified_return_reminder_at' => now()]);
                $sent++;
            });

        return $sent;
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
