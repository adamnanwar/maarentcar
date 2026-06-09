<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Models\Car;
use App\Models\TourPackage;
use App\Models\User;
use Inertia\Inertia;
use Inertia\Response;

class AdminDashboardController extends Controller
{
    public function index(): Response
    {
        $stats = [
            'total_cars' => Car::count(),
            'active_cars' => Car::where('is_active', true)->count(),
            'total_packages' => TourPackage::count(),
            'active_packages' => TourPackage::where('is_active', true)->count(),
            'total_bookings' => Booking::count(),
            'pending_verification' => Booking::where('status', Booking::STATUS_PENDING_VERIFICATION)->count(),
            'pending_payment' => Booking::where('status', Booking::STATUS_PENDING_PAYMENT)->count(),
            'total_revenue' => Booking::where('status', Booking::STATUS_PAID)
                ->orWhere('status', Booking::STATUS_COMPLETED)
                ->sum('total_price'),
            'total_customers' => User::where('role', User::ROLE_CUSTOMER)->count(),
        ];

        $recentBookings = Booking::with(['user', 'car'])
            ->orderBy('created_at', 'desc')
            ->take(10)
            ->get();

        return Inertia::render('Admin/Dashboard', [
            'stats' => $stats,
            'recentBookings' => $recentBookings,
        ]);
    }
}
