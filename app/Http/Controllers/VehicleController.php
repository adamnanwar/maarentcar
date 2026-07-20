<?php

namespace App\Http\Controllers;

use App\Models\Vehicle;
use App\Models\VehicleCategory;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class VehicleController extends Controller
{
    public function index(Request $request): Response
    {
        $query = Vehicle::query()
            ->with(['category', 'images'])
            ->where('is_active', true);

        if ($search = $request->string('search')->trim()->value()) {
            $query->where('name', 'ilike', "%{$search}%");
        }

        if ($categoryId = $request->integer('category_id')) {
            $query->where('category_id', $categoryId);
        }

        if ($transmission = $request->string('transmission')->value()) {
            $query->where('transmission', $transmission);
        }

        if ($seats = $request->integer('seats')) {
            $query->where('seat_capacity', '>=', $seats);
        }

        if ($maxPrice = $request->integer('max_price')) {
            $query->where('price_per_day', '<=', $maxPrice);
        }

        $vehicles = $query->orderBy('price_per_day')
            ->paginate(12)
            ->withQueryString();

        $vehicles->getCollection()->transform(fn (Vehicle $vehicle) => $vehicle->makeHidden('plate_number'));

        return Inertia::render('Mobil/Index', [
            'vehicles' => $vehicles,
            'categories' => VehicleCategory::orderBy('name')->get(),
            'filters' => $request->only(['search', 'category_id', 'transmission', 'seats', 'max_price']),
        ]);
    }

    public function show(Vehicle $vehicle): Response
    {
        abort_unless($vehicle->is_active, 404);

        $vehicle->load(['category', 'images']);
        $vehicle->makeHidden('plate_number');

        $reviews = $vehicle->reviews()->with('user')->where('is_hidden', false)->latest()->get();

        return Inertia::render('Mobil/Show', [
            'vehicle' => $vehicle,
            'reviews' => $reviews,
            'reviewsAvg' => round($reviews->avg('rating') ?? 0, 1),
        ]);
    }
}
