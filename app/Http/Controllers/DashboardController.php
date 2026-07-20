<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class DashboardController extends Controller
{
    public function index(Request $request): Response
    {
        $user = $request->user();

        $bookings = $user->bookings()
            ->with(['vehicle', 'package'])
            ->latest()
            ->take(5)
            ->get();

        return Inertia::render('Dashboard', [
            'recentBookings' => $bookings,
            'stats' => [
                'total_bookings' => $user->bookings()->count(),
                'active_bookings' => $user->bookings()->whereIn('status', ['menunggu_pembayaran', 'menunggu_verifikasi', 'dikonfirmasi', 'berlangsung'])->count(),
                'completed_bookings' => $user->bookings()->where('status', 'selesai')->count(),
            ],
        ]);
    }
}
