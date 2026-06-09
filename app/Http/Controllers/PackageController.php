<?php

namespace App\Http\Controllers;

use App\Models\TourPackage;
use App\Models\Car;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class PackageController extends Controller
{
    public function index(Request $request): Response
    {
        $query = TourPackage::where('is_active', true);

        // Search filter
        if ($request->filled('search')) {
            $query->where('name', 'like', "%{$request->search}%");
        }

        // Duration filter
        if ($request->filled('duration')) {
            $query->where('duration_days', $request->duration);
        }

        $packages = $query->orderBy('created_at', 'desc')->paginate(12)->withQueryString();

        return Inertia::render('Packages/Index', [
            'packages' => $packages,
            'filters' => $request->only(['search', 'duration']),
        ]);
    }

    public function show(TourPackage $package): Response
    {
        if (!$package->is_active) {
            abort(404);
        }

        $package->load('itineraries');

        // Get available cars for bundling
        $availableCars = Car::where('is_active', true)
            ->orderBy('price_per_day')
            ->get();

        return Inertia::render('Packages/Show', [
            'package' => $package,
            'availableCars' => $availableCars,
        ]);
    }
}
