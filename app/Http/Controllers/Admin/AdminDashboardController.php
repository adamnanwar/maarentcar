<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Models\Destination;
use App\Models\TourPackage;
use App\Models\Vehicle;
use Inertia\Inertia;
use Inertia\Response;

class AdminDashboardController extends Controller
{
    public function index(): Response
    {
        return Inertia::render('Admin/Dashboard', [
            'stats' => [
                'total_vehicles' => Vehicle::count(),
                'active_vehicles' => Vehicle::where('is_active', true)->where('status', Vehicle::STATUS_TERSEDIA)->count(),
                'total_destinations' => Destination::count(),
                'total_packages' => TourPackage::count(),
                'bookings_menunggu_verifikasi' => Booking::where('status', Booking::STATUS_MENUNGGU_VERIFIKASI)->count(),
                'bookings_berlangsung' => Booking::where('status', Booking::STATUS_BERLANGSUNG)->count(),
            ],
        ]);
    }
}
