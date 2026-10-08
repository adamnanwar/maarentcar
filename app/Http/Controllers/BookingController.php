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
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;
use Inertia\Response;
use Symfony\Component\HttpFoundation\StreamedResponse;

class BookingController extends Controller
{
    public function __construct(protected BookingService $bookingService)
    {
    }

    public function createForVehicle(Request $request, Vehicle $vehicle): Response|RedirectResponse
    {
        abort_unless($vehicle->is_active, 404);

        Booking::sweepOverduePayments();

        $activeBooking = $request->user()->bookings()
            ->where('booking_type', Booking::TYPE_MOBIL)
            ->whereIn('status', Booking::activeStatuses())
            ->latest()
            ->first();

        if ($activeBooking) {
            return redirect()->route('booking.show', $activeBooking)->with(
                'error',
                "Anda masih memiliki pesanan sewa mobil yang aktif ({$activeBooking->booking_code}). Selesaikan atau batalkan pesanan tersebut terlebih dahulu sebelum menyewa mobil baru."
            );
        }

        return Inertia::render('Booking/CreateMobil', [
            'vehicle' => $vehicle->load('images')->makeHidden('plate_number'),
            'destinations' => Destination::where('is_active', true)->orderBy('name')->get(),
        ]);
    }

    public function createForPackage(Request $request, TourPackage $tourPackage): Response
    {
        abort_unless($tourPackage->is_active, 404);

        Booking::sweepOverduePayments();

        $existingPackageBooking = $request->user()->bookings()
            ->where('booking_type', Booking::TYPE_PAKET_WISATA)
            ->whereIn('status', Booking::activeStatuses())
            ->with('package')
            ->latest()
            ->first();

        return Inertia::render('Booking/CreatePaket', [
            'package' => $tourPackage->load('destinations'),
            'existingPackageBooking' => $existingPackageBooking ? [
                'booking_code' => $existingPackageBooking->booking_code,
                'package_name' => $existingPackageBooking->package?->name,
            ] : null,
        ]);
    }

    public function store(StoreBookingRequest $request): RedirectResponse
    {
        $data = $request->validated();

        $booking = $data['booking_type'] === Booking::TYPE_MOBIL
            ? $this->bookingService->createVehicleBooking($request->user(), $data, $request->file('ktp_photo'))
            : $this->bookingService->createPackageBooking($request->user(), $data);

        return redirect()->route('booking.show', $booking)->with('success', 'Booking berhasil dibuat. Silakan lakukan pembayaran dalam waktu 1 jam.');
    }

    public function index(Request $request): Response
    {
        Booking::sweepOverduePayments();

        $bookings = $request->user()->bookings()
            ->with(['vehicle', 'package', 'review'])
            ->latest()
            ->paginate(10);

        return Inertia::render('Booking/Index', ['bookings' => $bookings]);
    }

    public function show(Request $request, Booking $booking): Response
    {
        abort_unless($booking->user_id === $request->user()->id, 403);

        $booking->expireIfOverdue();

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

    public function showKtp(Request $request, Booking $booking): StreamedResponse
    {
        $user = $request->user();
        $isOwner = $booking->user_id === $user->id;
        abort_unless($isOwner || $user->isAdminOrStaff(), 403);

        abort_unless($booking->ktp_photo_path, 404);
        abort_unless(Storage::disk('local')->exists($booking->ktp_photo_path), 404);

        return Storage::disk('local')->response($booking->ktp_photo_path);
    }

    public function cancel(Request $request, Booking $booking): RedirectResponse
    {
        abort_unless($booking->user_id === $request->user()->id, 403);
        abort_unless($booking->canBeCancelledByCustomer(), 403);

        $booking->transitionTo(Booking::STATUS_DIBATALKAN, $request->user(), 'Dibatalkan oleh pelanggan.');

        return back()->with('success', 'Booking berhasil dibatalkan.');
    }
}
