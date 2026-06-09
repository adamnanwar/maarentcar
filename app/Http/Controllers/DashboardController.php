<?php

namespace App\Http\Controllers;

use App\Models\Booking;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class DashboardController extends Controller
{
    public function index(Request $request): Response
    {
        $bookings = $request->user()
            ->bookings()
            ->with(['car', 'tourPackage'])
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
}
