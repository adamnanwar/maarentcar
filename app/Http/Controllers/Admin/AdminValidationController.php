<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Notifications\BookingVerifiedNotification;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class AdminValidationController extends Controller
{
    public function index(): Response
    {
        $bookings = Booking::with(['user', 'car', 'tourPackage'])
            ->where('status', Booking::STATUS_PENDING_VERIFICATION)
            ->orderBy('created_at', 'asc')
            ->paginate(10);

        return Inertia::render('Admin/Validations/Index', [
            'bookings' => $bookings,
        ]);
    }

    public function show(Booking $booking): Response
    {
        $booking->load(['user', 'car', 'tourPackage']);

        return Inertia::render('Admin/Validations/Show', [
            'booking' => $booking,
        ]);
    }

    public function update(Request $request, Booking $booking): RedirectResponse
    {
        $validated = $request->validate([
            'decision' => ['required', 'in:approve,reject'],
            'admin_note' => ['required_if:decision,reject', 'nullable', 'string', 'max:1000'],
        ]);

        if ($booking->status !== Booking::STATUS_PENDING_VERIFICATION) {
            return back()->withErrors(['error' => 'Booking ini sudah diverifikasi.']);
        }

        $isApproved = $validated['decision'] === 'approve';

        $booking->update([
            'status' => $isApproved ? Booking::STATUS_PENDING_PAYMENT : Booking::STATUS_REJECTED,
            'admin_note' => $validated['admin_note'] ?? ($isApproved ? 'Dokumen valid' : null),
            'payment_deadline' => $isApproved ? now()->addDays(1) : null,
        ]);

        // Notify user
        $booking->user->notify(new BookingVerifiedNotification($booking, $isApproved));

        $message = $isApproved
            ? 'Dokumen berhasil diverifikasi. Customer dapat melakukan pembayaran.'
            : 'Dokumen ditolak.';

        return redirect()->route('admin.validations.index')->with('success', $message);
    }

    public function allBookings(Request $request): Response
    {
        $query = Booking::with(['user', 'car', 'tourPackage']);

        if ($request->filled('search')) {
            $query->where(function ($q) use ($request) {
                $q->where('order_id', 'like', "%{$request->search}%")
                  ->orWhereHas('user', fn ($q) => $q->where('name', 'like', "%{$request->search}%"));
            });
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $bookings = $query->orderBy('created_at', 'desc')->paginate(15)->withQueryString();

        return Inertia::render('Admin/Bookings/Index', [
            'bookings' => $bookings,
            'filters' => $request->only(['search', 'status']),
        ]);
    }
}
