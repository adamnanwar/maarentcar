<?php

namespace App\Http\Controllers;

use App\Models\Destination;
use App\Models\Review;
use App\Models\TourPackage;
use App\Models\Vehicle;
use Inertia\Inertia;
use Inertia\Response;

class HomeController extends Controller
{
    public function index(): Response
    {
        $vehicles = Vehicle::query()
            ->with(['category', 'images'])
            ->where('is_active', true)
            ->where('status', Vehicle::STATUS_TERSEDIA)
            ->latest()
            ->take(6)
            ->get()
            ->makeHidden('plate_number');

        $packages = TourPackage::query()
            ->with('destinations')
            ->where('is_active', true)
            ->latest()
            ->take(4)
            ->get();

        $destinations = Destination::query()
            ->where('is_active', true)
            ->latest()
            ->take(6)
            ->get();

        $testimonials = Review::query()
            ->where('is_hidden', false)
            ->with('user')
            ->latest()
            ->take(2)
            ->get();

        return Inertia::render('Home', [
            'featuredVehicles' => $vehicles,
            'featuredPackages' => $packages,
            'popularDestinations' => $destinations,
            'testimonials' => $testimonials,
        ]);
    }
}
