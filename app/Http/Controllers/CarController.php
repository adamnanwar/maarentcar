<?php

namespace App\Http\Controllers;

use App\Models\Car;
use App\Models\TourPackage;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class CarController extends Controller
{
    public function index(Request $request): Response
    {
        $query = Car::where('is_active', true);

        // Search filter
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('brand', 'like', "%{$search}%");
            });
        }

        // Type filter
        if ($request->filled('type') && $request->type !== 'all') {
            $query->where('type', $request->type);
        }

        // Transmission filter
        if ($request->filled('transmission') && $request->transmission !== 'all') {
            $query->where('transmission', $request->transmission);
        }

        // Seats filter
        if ($request->filled('seats')) {
            $query->where('seats', '>=', $request->seats);
        }

        // Price range filter
        if ($request->filled('min_price')) {
            $query->where('price_per_day', '>=', $request->min_price);
        }
        if ($request->filled('max_price')) {
            $query->where('price_per_day', '<=', $request->max_price);
        }

        $cars = $query->orderBy('created_at', 'desc')->paginate(12)->withQueryString();

        // Get unique types for filter
        $types = Car::where('is_active', true)
            ->distinct()
            ->pluck('type')
            ->filter()
            ->values();

        return Inertia::render('Cars/Index', [
            'cars' => $cars,
            'types' => $types,
            'filters' => $request->only(['search', 'type', 'transmission', 'seats', 'min_price', 'max_price']),
        ]);
    }

    public function show(Car $car): Response
    {
        if (!$car->is_active) {
            abort(404);
        }

        $packages = TourPackage::where('is_active', true)->get();

        return Inertia::render('Cars/Show', [
            'car' => $car,
            'packages' => $packages,
        ]);
    }
}
