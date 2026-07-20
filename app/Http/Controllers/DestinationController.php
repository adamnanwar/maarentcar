<?php

namespace App\Http\Controllers;

use App\Models\Destination;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class DestinationController extends Controller
{
    public function index(Request $request): Response
    {
        $query = Destination::query()->where('is_active', true);

        if ($search = $request->string('search')->trim()->value()) {
            $query->where('name', 'ilike', "%{$search}%");
        }

        if ($category = $request->string('category')->value()) {
            $query->where('category', $category);
        }

        $destinations = $query->orderBy('name')->paginate(12)->withQueryString();

        return Inertia::render('Destinasi/Index', [
            'destinations' => $destinations,
            'filters' => $request->only(['search', 'category']),
        ]);
    }

    public function show(Destination $destination): Response
    {
        abort_unless($destination->is_active, 404);

        $destination->load(['tourPackages' => fn ($q) => $q->where('is_active', true)]);

        return Inertia::render('Destinasi/Show', [
            'destination' => $destination,
        ]);
    }
}
