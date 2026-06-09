<?php

namespace App\Http\Controllers;

use App\Models\Booking;
use App\Models\Car;
use App\Models\TourPackage;
use App\Notifications\BookingCreatedNotification;
use App\Services\PaymentService;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Inertia\Inertia;
use Inertia\Response;

class BookingController extends Controller
{
    public function __construct(
        private PaymentService $paymentService
    ) {}

    public function index(): Response
    {
        $bookings = Booking::with(['car', 'tourPackage'])
            ->where('user_id', auth()->id())
            ->orderBy('created_at', 'desc')
            ->get();

        return Inertia::render('Dashboard', [
            'bookings' => $bookings,
            'stats' => [
                'total' => $bookings->count(),
                'pending_verification' => $bookings->where('status', Booking::STATUS_PENDING_VERIFICATION)->count(),
                'pending_payment' => $bookings->where('status', Booking::STATUS_PENDING_PAYMENT)->count(),
                'paid' => $bookings->where('status', Booking::STATUS_PAID)->count(),
                'completed' => $bookings->where('status', Booking::STATUS_COMPLETED)->count(),
            ],
        ]);
    }

    public function create(Request $request): Response
    {
        $carId = $request->query('car_id');
        $car = $carId ? Car::find($carId) : null;
        $packages = TourPackage::where('is_active', true)->get();

        return Inertia::render('Bookings/Create', [
            'car' => $car,
            'packages' => $packages,
        ]);
    }

    public function show(Booking $booking): Response
    {
        // Ensure user owns the booking
        if ($booking->user_id !== auth()->id()) {
            abort(403);
        }

        $booking->load(['car', 'tourPackage', 'payment']);

        return Inertia::render('Bookings/Show', [
            'booking' => $booking,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'car_id' => ['required', 'uuid', 'exists:cars,id'],
            'tour_package_id' => ['nullable', 'uuid', 'exists:tour_packages,id'],
            'start_date' => ['required', 'date', 'after_or_equal:today'],
            'end_date' => ['required', 'date', 'after_or_equal:start_date'],
            'pickup_location' => ['nullable', 'string', 'max:255'],
            'use_driver' => ['boolean'],
            'notes' => ['nullable', 'string', 'max:1000'],
            'ktp_image' => ['required', 'image', 'mimes:jpg,jpeg,png', 'max:5120'],
            'sim_image' => ['nullable', 'image', 'mimes:jpg,jpeg,png', 'max:5120'],
        ]);

        // If not using driver, SIM is required
        if (!$request->boolean('use_driver') && !$request->hasFile('sim_image')) {
            return back()->withErrors(['sim_image' => 'SIM wajib diupload jika memilih lepas kunci.']);
        }

        $car = Car::findOrFail($validated['car_id']);

        // Check car availability
        $startDate = Carbon::parse($validated['start_date']);
        $endDate = Carbon::parse($validated['end_date']);
        $durationDays = $startDate->diffInDays($endDate) + 1;

        $isAvailable = !Booking::where('car_id', $car->id)
            ->whereNotIn('status', [Booking::STATUS_CANCELLED, Booking::STATUS_REJECTED])
            ->where(function ($q) use ($startDate, $endDate) {
                $q->whereBetween('start_date', [$startDate, $endDate])
                    ->orWhereBetween('end_date', [$startDate, $endDate])
                    ->orWhere(function ($q2) use ($startDate, $endDate) {
                        $q2->where('start_date', '<=', $startDate)
                            ->where('end_date', '>=', $endDate);
                    });
            })
            ->exists();

        if (!$isAvailable) {
            return back()->withErrors(['car_id' => 'Mobil tidak tersedia untuk tanggal yang dipilih.']);
        }

        // Calculate total price
        $pricePerDay = $request->boolean('use_driver')
            ? ($car->with_driver_price_per_day ?? $car->price_per_day + 150000)
            : $car->price_per_day;

        $totalPrice = $pricePerDay * $durationDays;

        // Add tour package price if selected
        if (!empty($validated['tour_package_id'])) {
            $tourPackage = TourPackage::find($validated['tour_package_id']);
            if ($tourPackage) {
                $totalPrice += $tourPackage->price;
            }
        }

        // Upload documents
        $ktpPath = $request->file('ktp_image')->store('documents/ktp', 'public');
        $simPath = $request->hasFile('sim_image')
            ? $request->file('sim_image')->store('documents/sim', 'public')
            : null;

        DB::beginTransaction();
        try {
            $booking = Booking::create([
                'order_id' => 'ORD-' . strtoupper(Str::random(8)),
                'user_id' => auth()->id(),
                'car_id' => $car->id,
                'tour_package_id' => $validated['tour_package_id'] ?? null,
                'start_date' => $startDate,
                'end_date' => $endDate,
                'pickup_location' => $validated['pickup_location'] ?? null,
                'use_driver' => $request->boolean('use_driver'),
                'notes' => $validated['notes'] ?? null,
                'ktp_image_url' => Storage::url($ktpPath),
                'sim_image_url' => $simPath ? Storage::url($simPath) : null,
                'total_price' => $totalPrice,
                'status' => Booking::STATUS_PENDING_VERIFICATION,
                'payment_deadline' => now()->addDays(1),
            ]);

            // Notify user
            $request->user()->notify(new BookingCreatedNotification($booking));

            DB::commit();

            return redirect()->route('bookings.show', $booking)
                ->with('success', 'Booking berhasil dibuat! Menunggu verifikasi dokumen oleh admin.');

        } catch (\Exception $e) {
            DB::rollBack();

            // Clean up uploaded files
            Storage::disk('public')->delete($ktpPath);
            if ($simPath) {
                Storage::disk('public')->delete($simPath);
            }

            return back()->withErrors(['error' => 'Terjadi kesalahan. Silakan coba lagi.']);
        }
    }

    public function createPayment(Booking $booking): RedirectResponse
    {
        if ($booking->user_id !== auth()->id()) {
            abort(403);
        }

        if (!$booking->canBePaid()) {
            return back()->withErrors(['error' => 'Booking ini tidak dapat dibayar.']);
        }

        try {
            // Generate snap token if not exists
            if (!$booking->snap_token) {
                $snapToken = $this->paymentService->createSnapToken($booking);
                $booking->update(['snap_token' => $snapToken]);
            }

            return back()->with('snap_token', $booking->snap_token);

        } catch (\Exception $e) {
            return back()->withErrors(['error' => 'Gagal memproses pembayaran. Silakan coba lagi.']);
        }
    }

    public function cancel(Booking $booking): RedirectResponse
    {
        if ($booking->user_id !== auth()->id()) {
            abort(403);
        }

        if (!$booking->canBeCancelled()) {
            return back()->withErrors(['error' => 'Booking ini tidak dapat dibatalkan.']);
        }

        $booking->update(['status' => Booking::STATUS_CANCELLED]);

        return back()->with('success', 'Booking berhasil dibatalkan.');
    }
}
