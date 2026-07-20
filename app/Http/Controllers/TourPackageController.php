<?php

namespace App\Http\Controllers;

use App\Models\TourPackage;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class TourPackageController extends Controller
{
    public function index(Request $request): Response
    {
        $query = TourPackage::query()->with('destinations')->where('is_active', true);

        if ($search = $request->string('search')->trim()->value()) {
            $query->where('name', 'ilike', "%{$search}%");
        }

        if ($duration = $request->integer('duration_days')) {
            $query->where('duration_days', $duration);
        }

        $packages = $query->orderBy('price')->paginate(12)->withQueryString();

        return Inertia::render('PaketWisata/Index', [
            'packages' => $packages,
            'filters' => $request->only(['search', 'duration_days']),
        ]);
    }

    public function show(TourPackage $tourPackage): Response
    {
        abort_unless($tourPackage->is_active, 404);

        $tourPackage->load(['destinations', 'vehicle']);

        $reviews = $tourPackage->reviews()->with('user')->where('is_hidden', false)->latest()->get();

        return Inertia::render('PaketWisata/Show', [
            'package' => $tourPackage,
            'reviews' => $reviews,
            'reviewsAvg' => round($reviews->avg('rating') ?? 0, 1),
        ]);
    }
}
