<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;
use Illuminate\Validation\Rule;
use Inertia\Inertia;
use Inertia\Response;

class AdminBookingController extends Controller implements HasMiddleware
{
    public static function middleware(): array
    {
        return [
            new Middleware('permission:bookings.view', only: ['index', 'show']),
            new Middleware('permission:bookings.update_status', only: ['updateStatus']),
        ];
    }

    public function index(Request $request): Response
    {
        Booking::sweepOverduePayments();

        $query = Booking::query()->with(['user', 'vehicle', 'package']);

        if ($search = $request->string('search')->trim()->value()) {
            $query->where(function ($q) use ($search) {
                $q->where('booking_code', 'ilike', "%{$search}%")
                    ->orWhereHas('user', fn ($u) => $u->where('name', 'ilike', "%{$search}%"));
            });
        }

        if ($status = $request->string('status')->value()) {
            $query->where('status', $status);
        }

        if ($type = $request->string('booking_type')->value()) {
            $query->where('booking_type', $type);
        }

        return Inertia::render('Admin/Booking/Index', [
            'bookings' => $query->latest()->paginate(15)->withQueryString(),
            'filters' => $request->only(['search', 'status', 'booking_type']),
        ]);
    }

    public function show(Booking $booking): Response
    {
        $booking->expireIfOverdue();

        $booking->load(['user', 'vehicle.images', 'package', 'destinations.destination', 'payments.verifications.verifiedBy', 'statusLogs.changedBy']);

        return Inertia::render('Admin/Booking/Show', ['booking' => $booking]);
    }

    public function updateStatus(Request $request, Booking $booking): RedirectResponse
    {
        $data = $request->validate([
            'status' => ['required', Rule::in([
                Booking::STATUS_BERLANGSUNG,
                Booking::STATUS_SELESAI,
                Booking::STATUS_DIBATALKAN,
            ])],
            'internal_notes' => ['nullable', 'string', 'max:1000'],
        ]);

        $this->assertValidTransition($booking->status, $data['status']);

        if (! empty($data['internal_notes'])) {
            $booking->update(['internal_notes' => $data['internal_notes']]);
        }

        $booking->transitionTo($data['status'], $request->user());

        return back()->with('success', 'Status booking berhasil diperbarui.');
    }

    protected function assertValidTransition(string $from, string $to): void
    {
        $allowed = match ($from) {
            Booking::STATUS_DIKONFIRMASI => [Booking::STATUS_BERLANGSUNG, Booking::STATUS_DIBATALKAN],
            Booking::STATUS_BERLANGSUNG => [Booking::STATUS_SELESAI],
            Booking::STATUS_MENUNGGU_PEMBAYARAN, Booking::STATUS_MENUNGGU_VERIFIKASI => [Booking::STATUS_DIBATALKAN],
            default => [],
        };

        abort_unless(in_array($to, $allowed, true), 422, "Booking tidak bisa diubah dari status {$from} ke {$to}.");
    }
}
