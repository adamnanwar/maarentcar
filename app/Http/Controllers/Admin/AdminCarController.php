<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Car;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;
use Inertia\Response;

class AdminCarController extends Controller
{
    public function index(Request $request): Response
    {
        $query = Car::query();

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('brand', 'like', "%{$search}%");
            });
        }

        if ($request->filled('type')) {
            $query->where('type', $request->type);
        }

        $cars = $query->orderBy('created_at', 'desc')->paginate(10)->withQueryString();

        $types = Car::distinct()->pluck('type')->filter()->values();

        return Inertia::render('Admin/Cars/Index', [
            'cars' => $cars,
            'types' => $types,
            'filters' => $request->only(['search', 'type']),
        ]);
    }

    public function create(): Response
    {
        return Inertia::render('Admin/Cars/Create');
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'brand' => ['required', 'string', 'max:255'],
            'type' => ['required', 'string', 'max:50'],
            'transmission' => ['required', 'in:Manual,Automatic'],
            'seats' => ['required', 'integer', 'min:2', 'max:12'],
            'price_per_day' => ['required', 'integer', 'min:0'],
            'with_driver_price_per_day' => ['nullable', 'integer', 'min:0'],
            'thumbnail' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:5120'],
            'is_active' => ['boolean'],
        ]);

        if ($request->hasFile('thumbnail')) {
            $path = $request->file('thumbnail')->store('cars', 'public');
            $validated['thumbnail_url'] = Storage::url($path);
        }

        unset($validated['thumbnail']);
        $validated['is_active'] = $request->boolean('is_active', true);

        Car::create($validated);

        return redirect()->route('admin.cars.index')->with('success', 'Mobil berhasil ditambahkan.');
    }

    public function edit(Car $car): Response
    {
        return Inertia::render('Admin/Cars/Edit', [
            'car' => $car,
        ]);
    }

    public function update(Request $request, Car $car): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'brand' => ['required', 'string', 'max:255'],
            'type' => ['required', 'string', 'max:50'],
            'transmission' => ['required', 'in:Manual,Automatic'],
            'seats' => ['required', 'integer', 'min:2', 'max:12'],
            'price_per_day' => ['required', 'integer', 'min:0'],
            'with_driver_price_per_day' => ['nullable', 'integer', 'min:0'],
            'thumbnail' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:5120'],
            'is_active' => ['boolean'],
        ]);

        if ($request->hasFile('thumbnail')) {
            // Delete old thumbnail
            if ($car->thumbnail_url) {
                $oldPath = str_replace('/storage/', '', $car->thumbnail_url);
                Storage::disk('public')->delete($oldPath);
            }

            $path = $request->file('thumbnail')->store('cars', 'public');
            $validated['thumbnail_url'] = Storage::url($path);
        }

        unset($validated['thumbnail']);
        $validated['is_active'] = $request->boolean('is_active', true);

        $car->update($validated);

        return redirect()->route('admin.cars.index')->with('success', 'Mobil berhasil diperbarui.');
    }

    public function destroy(Car $car): RedirectResponse
    {
        // Soft delete - just deactivate
        $car->update(['is_active' => false]);

        return redirect()->route('admin.cars.index')->with('success', 'Mobil berhasil dinonaktifkan.');
    }
}
