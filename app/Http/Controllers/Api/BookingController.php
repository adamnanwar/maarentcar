<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\UpdateBookingStatusRequest;
use App\Http\Requests\Booking\CancelBookingRequest;
use App\Http\Requests\Booking\StoreBookingRequest;
use App\Models\Booking;
use App\Services\BookingService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class BookingController extends Controller
{
    public function __construct(
        private BookingService $bookingService
    ) {}

    /**
     * Create a new booking.
     */
    public function store(StoreBookingRequest $request): JsonResponse
    {
        $result = $this->bookingService->createBooking(
            $request->user(),
            $request->validated()
        );

        if (isset($result['error'])) {
            return response()->json([
                'message' => $result['error'],
            ], 422);
        }

        return response()->json([
            'message' => 'Booking berhasil dibuat.',
            'booking' => $result['booking'],
            'snap_token' => $result['snap_token'],
        ], 201);
    }

    /**
     * List bookings for the authenticated user.
     */
    public function myBookings(Request $request): JsonResponse
    {
        $bookings = Booking::where('user_id', $request->user()->id)
            ->with(['car', 'payment'])
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json([
            'bookings' => $bookings,
        ]);
    }

    /**
     * Get booking details.
     */
    public function show(Request $request, string $id): JsonResponse
    {
        $booking = Booking::where('id', $id)
            ->where('user_id', $request->user()->id)
            ->with(['car', 'payment'])
            ->firstOrFail();

        return response()->json([
            'booking' => $booking,
        ]);
    }

    /**
     * Cancel a booking.
     */
    public function cancel(CancelBookingRequest $request, string $id): JsonResponse
    {
        $booking = Booking::where('id', $id)
            ->where('user_id', $request->user()->id)
            ->firstOrFail();

        if ($booking->status !== 'PENDING_PAYMENT') {
            return response()->json([
                'message' => 'Hanya booking dengan status PENDING_PAYMENT yang dapat dibatalkan.',
            ], 422);
        }

        try {
            $booking = $this->bookingService->cancelBooking($booking, $request->reason);

            return response()->json([
                'message' => 'Booking berhasil dibatalkan.',
                'booking' => $booking,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'message' => $e->getMessage(),
            ], 422);
        }
    }

    /**
     * List all bookings (Admin only).
     */
    public function indexAdmin(Request $request): JsonResponse
    {
        $query = Booking::with(['car', 'payment', 'user']);

        // Filter by status
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        // Filter by date range
        if ($request->filled('from')) {
            $query->where('start_date', '>=', $request->from);
        }
        if ($request->filled('to')) {
            $query->where('end_date', '<=', $request->to);
        }

        // Search by order_id or user name
        if ($request->filled('q')) {
            $search = $request->q;
            $query->where(function ($q) use ($search) {
                $q->where('order_id', 'like', "%{$search}%")
                    ->orWhereHas('user', function ($userQuery) use ($search) {
                        $userQuery->where('name', 'like', "%{$search}%");
                    });
            });
        }

        $bookings = $query->orderBy('created_at', 'desc')->get();

        return response()->json([
            'bookings' => $bookings,
        ]);
    }

    /**
     * Update booking status (Admin only).
     */
    public function updateStatus(UpdateBookingStatusRequest $request, string $id): JsonResponse
    {
        $booking = Booking::findOrFail($id);

        try {
            $booking = $this->bookingService->updateStatus(
                $booking,
                $request->status,
                $request->note
            );

            return response()->json([
                'message' => 'Status booking berhasil diperbarui.',
                'booking' => $booking,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'message' => $e->getMessage(),
            ], 422);
        }
    }
}
