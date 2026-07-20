<?php

namespace App\Services;

use App\Models\Booking;
use App\Models\Destination;
use App\Models\TourPackage;
use App\Models\User;
use App\Models\Vehicle;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class BookingService
{
    public function createVehicleBooking(User $user, array $data): Booking
    {
        $vehicle = Vehicle::where('is_active', true)->findOrFail($data['vehicle_id']);

        $start = Carbon::parse($data['start_datetime']);
        $end = Carbon::parse($data['end_datetime']);
        $durationDays = max(1, (int) ceil($start->diffInHours($end) / 24));

        $withDriver = (bool) $data['with_driver'];
        $deliveryMethod = $data['delivery_method'];

        $this->assertValidDeliveryMethod($withDriver, $deliveryMethod);

        if (! $vehicle->isAvailableFor($start, $end)) {
            throw ValidationException::withMessages([
                'start_datetime' => 'Mobil ini sudah dipesan pada rentang tanggal tersebut. Silakan pilih tanggal lain.',
            ]);
        }

        $basePrice = $vehicle->price_per_day * $durationDays;
        $driverFee = $withDriver ? $vehicle->driver_fee_per_day * $durationDays : 0;
        $deliveryFee = $deliveryMethod === Booking::DELIVERY_DELIVERED_TO_ADDRESS ? $vehicle->base_delivery_fee : 0;

        $destinationIds = $withDriver ? array_values($data['destination_ids'] ?? []) : [];
        $destinations = $destinationIds ? Destination::whereIn('id', $destinationIds)->where('is_active', true)->get() : collect();
        $addonTotal = $destinations->sum('addon_price');

        $totalPrice = $basePrice + $driverFee + $deliveryFee + $addonTotal;

        return DB::transaction(function () use (
            $user, $vehicle, $start, $end, $durationDays, $withDriver, $deliveryMethod,
            $basePrice, $driverFee, $deliveryFee, $addonTotal, $totalPrice, $destinations, $data
        ) {
            $booking = Booking::create([
                'booking_code' => $this->generateBookingCode(),
                'user_id' => $user->id,
                'booking_type' => Booking::TYPE_MOBIL,
                'vehicle_id' => $vehicle->id,
                'start_datetime' => $start,
                'end_datetime' => $end,
                'duration_days' => $durationDays,
                'with_driver' => $withDriver,
                'delivery_method' => $deliveryMethod,
                'pickup_address_snapshot' => $this->buildAddressSnapshot($deliveryMethod, $data),
                'passenger_count' => $data['passenger_count'] ?? null,
                'base_price' => $basePrice,
                'driver_fee' => $driverFee,
                'delivery_fee' => $deliveryFee,
                'addon_total' => $addonTotal,
                'total_price' => $totalPrice,
                'status' => Booking::STATUS_MENUNGGU_PEMBAYARAN,
                'notes' => $data['notes'] ?? null,
            ]);

            foreach ($destinations as $destination) {
                $booking->destinations()->create([
                    'destination_id' => $destination->id,
                    'price_at_booking' => $destination->addon_price,
                ]);
            }

            $booking->statusLogs()->create([
                'from_status' => null,
                'to_status' => Booking::STATUS_MENUNGGU_PEMBAYARAN,
                'changed_by' => $user->id,
                'note' => 'Booking dibuat oleh pelanggan.',
            ]);

            return $booking;
        });
    }

    public function createPackageBooking(User $user, array $data): Booking
    {
        $package = TourPackage::where('is_active', true)->with('vehicle')->findOrFail($data['package_id']);

        $start = Carbon::parse($data['start_datetime'])->startOfDay();
        $end = (clone $start)->addDays($package->duration_days);

        $totalPrice = $package->price;

        return DB::transaction(function () use ($user, $package, $start, $end, $totalPrice, $data) {
            $booking = Booking::create([
                'booking_code' => $this->generateBookingCode(),
                'user_id' => $user->id,
                'booking_type' => Booking::TYPE_PAKET_WISATA,
                'package_id' => $package->id,
                'vehicle_id' => $package->vehicle_id,
                'start_datetime' => $start,
                'end_datetime' => $end,
                'duration_days' => $package->duration_days,
                'with_driver' => true,
                'delivery_method' => Booking::DELIVERY_DRIVER_PICKUP,
                'pickup_address_snapshot' => $this->buildAddressSnapshot(Booking::DELIVERY_DRIVER_PICKUP, $data),
                'passenger_count' => $data['passenger_count'] ?? null,
                'base_price' => $totalPrice,
                'total_price' => $totalPrice,
                'status' => Booking::STATUS_MENUNGGU_PEMBAYARAN,
                'notes' => $data['notes'] ?? null,
            ]);

            $booking->statusLogs()->create([
                'from_status' => null,
                'to_status' => Booking::STATUS_MENUNGGU_PEMBAYARAN,
                'changed_by' => $user->id,
                'note' => 'Booking paket wisata dibuat oleh pelanggan.',
            ]);

            return $booking;
        });
    }

    protected function assertValidDeliveryMethod(bool $withDriver, string $deliveryMethod): void
    {
        $allowed = $withDriver
            ? [Booking::DELIVERY_DRIVER_PICKUP]
            : [Booking::DELIVERY_PICKUP_AT_OFFICE, Booking::DELIVERY_DELIVERED_TO_ADDRESS];

        if (! in_array($deliveryMethod, $allowed, true)) {
            throw ValidationException::withMessages([
                'delivery_method' => 'Metode pengambilan tidak valid untuk pilihan supir yang dipilih.',
            ]);
        }
    }

    protected function buildAddressSnapshot(string $deliveryMethod, array $data): ?array
    {
        if ($deliveryMethod === Booking::DELIVERY_PICKUP_AT_OFFICE) {
            return null;
        }

        return [
            'recipient_name' => $data['recipient_name'] ?? null,
            'phone' => $data['address_phone'] ?? null,
            'full_address' => $data['full_address'] ?? null,
            'district' => $data['district'] ?? null,
            'subdistrict' => $data['subdistrict'] ?? null,
            'landmark' => $data['landmark'] ?? null,
        ];
    }

    protected function generateBookingCode(): string
    {
        do {
            $code = 'WRC-'.now()->format('ymd').'-'.Str::upper(Str::random(5));
        } while (Booking::where('booking_code', $code)->exists());

        return $code;
    }
}
