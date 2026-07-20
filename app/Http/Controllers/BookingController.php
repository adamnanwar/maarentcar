<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreBookingRequest;
use App\Models\Booking;
use App\Models\Destination;
use App\Models\Setting;
use App\Models\TourPackage;
use App\Models\Vehicle;
use App\Services\BookingService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class BookingController extends Controller
{
    public function __construct(protected BookingService $bookingService)
    {
    }

    public function createForVehicle(Vehicle $vehicle): Response
    {
        abort_unless($vehicle->is_active, 404);

        return Inertia::render('Booking/CreateMobil', [
            'vehicle' => $vehicle->load('images')->makeHidden('plate_number'),
            'destinations' => Destination::where('is_active', true)->orderBy('name')->get(),
        ]);
    }

    public function createForPackage(TourPackage $tourPackage): Response
    {
        abort_unless($tourPackage->is_active, 404);

        return Inertia::render('Booking/CreatePaket', [
            'package' => $tourPackage->load('destinations'),
        ]);
    }

    public function store(StoreBookingRequest $request): RedirectResponse
    {
        $data = $request->validated();

        $booking = $data['booking_type'] === Booking::TYPE_MOBIL
            ? $this->bookingService->createVehicleBooking($request->user(), $data)
            : $this->bookingService->createPackageBooking($request->user(), $data);

        return redirect()->route('booking.show', $booking)->with('success', 'Booking berhasil dibuat. Silakan lakukan pembayaran.');
    }

    public function index(Request $request): Response
    {
        $bookings = $request->user()->bookings()
            ->with(['vehicle', 'package', 'review'])
            ->latest()
            ->paginate(10);

        return Inertia::render('Booking/Index', ['bookings' => $bookings]);
    }

    public function show(Request $request, Booking $booking): Response
    {
        abort_unless($booking->user_id === $request->user()->id, 403);

        $booking->load(['vehicle.images', 'package', 'destinations.destination', 'payments', 'statusLogs', 'review']);

        return Inertia::render('Booking/Show', [
            'booking' => $booking,
            'bankInfo' => [
                'bank_name' => Setting::get('bank_name'),
                'bank_account_number' => Setting::get('bank_account_number'),
                'bank_account_name' => Setting::get('bank_account_name'),
            ],
        ]);
    }

    public function cancel(Request $request, Booking $booking): RedirectResponse
    {
        abort_unless($booking->user_id === $request->user()->id, 403);
        abort_unless($booking->canBeCancelledByCustomer(), 403);

        $booking->transitionTo(Booking::STATUS_DIBATALKAN, $request->user(), 'Dibatalkan oleh pelanggan.');

        return back()->with('success', 'Booking berhasil dibatalkan.');
    }
}
