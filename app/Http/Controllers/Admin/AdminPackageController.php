<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\TourPackage;
use App\Models\TourItinerary;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;
use Inertia\Response;

class AdminPackageController extends Controller
{
    public function index(Request $request): Response
    {
        $query = TourPackage::query();

        if ($request->filled('search')) {
            $query->where('name', 'like', "%{$request->search}%");
        }

        $packages = $query->orderBy('created_at', 'desc')->paginate(10)->withQueryString();

        return Inertia::render('Admin/Packages/Index', [
            'packages' => $packages,
            'filters' => $request->only(['search']),
        ]);
    }

    public function create(): Response
    {
        return Inertia::render('Admin/Packages/Create');
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'price' => ['required', 'integer', 'min:0'],
            'duration_days' => ['required', 'integer', 'min:1'],
            'description' => ['nullable', 'string', 'max:5000'],
            'thumbnail' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:5120'],
            'is_active' => ['boolean'],
            'itineraries' => ['nullable', 'array'],
            'itineraries.*.day_number' => ['required_with:itineraries', 'integer', 'min:1'],
            'itineraries.*.activity' => ['required_with:itineraries', 'string', 'max:255'],
            'itineraries.*.time' => ['nullable', 'string', 'max:50'],
        ]);

        DB::beginTransaction();
        try {
            if ($request->hasFile('thumbnail')) {
                $path = $request->file('thumbnail')->store('packages', 'public');
                $validated['thumbnail_url'] = Storage::url($path);
            }

            unset($validated['thumbnail'], $validated['itineraries']);
            $validated['is_active'] = $request->boolean('is_active', true);

            $package = TourPackage::create($validated);

            // Create itineraries
            if ($request->has('itineraries')) {
                foreach ($request->itineraries as $itinerary) {
                    $package->itineraries()->create($itinerary);
                }
            }

            DB::commit();

            return redirect()->route('admin.packages.index')->with('success', 'Paket wisata berhasil ditambahkan.');

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withErrors(['error' => 'Terjadi kesalahan. Silakan coba lagi.']);
        }
    }

    public function edit(TourPackage $package): Response
    {
        $package->load('itineraries');

        return Inertia::render('Admin/Packages/Edit', [
            'package' => $package,
        ]);
    }

    public function update(Request $request, TourPackage $package): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'price' => ['required', 'integer', 'min:0'],
            'duration_days' => ['required', 'integer', 'min:1'],
            'description' => ['nullable', 'string', 'max:5000'],
            'thumbnail' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:5120'],
            'is_active' => ['boolean'],
            'itineraries' => ['nullable', 'array'],
            'itineraries.*.day_number' => ['required_with:itineraries', 'integer', 'min:1'],
            'itineraries.*.activity' => ['required_with:itineraries', 'string', 'max:255'],
            'itineraries.*.time' => ['nullable', 'string', 'max:50'],
        ]);

        DB::beginTransaction();
        try {
            if ($request->hasFile('thumbnail')) {
                // Delete old thumbnail
                if ($package->thumbnail_url) {
                    $oldPath = str_replace('/storage/', '', $package->thumbnail_url);
                    Storage::disk('public')->delete($oldPath);
                }

                $path = $request->file('thumbnail')->store('packages', 'public');
                $validated['thumbnail_url'] = Storage::url($path);
            }

            unset($validated['thumbnail'], $validated['itineraries']);
            $validated['is_active'] = $request->boolean('is_active', true);

            $package->update($validated);

            // Update itineraries - delete old and create new
            $package->itineraries()->delete();
            if ($request->has('itineraries')) {
                foreach ($request->itineraries as $itinerary) {
                    $package->itineraries()->create($itinerary);
                }
            }

            DB::commit();

            return redirect()->route('admin.packages.index')->with('success', 'Paket wisata berhasil diperbarui.');

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withErrors(['error' => 'Terjadi kesalahan. Silakan coba lagi.']);
        }
    }

    public function destroy(TourPackage $package): RedirectResponse
    {
        // Soft delete - just deactivate
        $package->update(['is_active' => false]);

        return redirect()->route('admin.packages.index')->with('success', 'Paket wisata berhasil dinonaktifkan.');
    }
}
