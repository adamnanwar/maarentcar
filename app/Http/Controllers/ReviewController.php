<?php

namespace App\Http\Controllers;

use App\Models\Booking;
use App\Models\Review;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class ReviewController extends Controller
{
    public function create(Request $request, Booking $booking): Response
    {
        abort_unless($booking->canBeReviewedBy($request->user()), 403);

        $booking->load(['vehicle', 'package']);

        return Inertia::render('Ulasan/Create', ['booking' => $booking]);
    }

    public function store(Request $request, Booking $booking): RedirectResponse
    {
        abort_unless($booking->canBeReviewedBy($request->user()), 403);

        $data = $request->validate([
            'rating' => ['required', 'integer', 'min:1', 'max:5'],
            'comment' => ['nullable', 'string', 'max:1000'],
        ]);

        Review::create([
            'booking_id' => $booking->id,
            'user_id' => $request->user()->id,
            'vehicle_id' => $booking->vehicle_id,
            'package_id' => $booking->package_id,
            'rating' => $data['rating'],
            'comment' => $data['comment'] ?? null,
        ]);

        return redirect()->route('booking.show', $booking)->with('success', 'Terima kasih atas ulasan Anda.');
    }
}
