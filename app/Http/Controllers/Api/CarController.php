<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreCarRequest;
use App\Http\Requests\Admin\UpdateCarRequest;
use App\Models\Car;
use App\Services\BookingService;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class CarController extends Controller
{
    /**
     * List all cars with optional filters.
     */
    public function index(Request $request): JsonResponse
    {
        $query = Car::query();

        // Search by name or brand
        if ($request->filled('q')) {
            $search = $request->q;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('brand', 'like', "%{$search}%");
            });
        }

        // Filter by type
        if ($request->filled('type')) {
            $query->where('type', $request->type);
        }

        // Filter by transmission
        if ($request->filled('transmission')) {
            $query->where('transmission', $request->transmission);
        }

        // Filter by minimum seats
        if ($request->filled('seats')) {
            $query->where('seats', '>=', $request->seats);
        }

        // Filter by price range
        if ($request->filled('min_price')) {
            $query->where('price_per_day', '>=', $request->min_price);
        }
        if ($request->filled('max_price')) {
            $query->where('price_per_day', '<=', $request->max_price);
        }

        // Filter active only (default: true)
        if ($request->boolean('active_only', true)) {
            $query->where('is_active', true);
        }

        $cars = $query->orderBy('created_at', 'desc')->get();

        return response()->json([
            'cars' => $cars,
        ]);
    }

    /**
     * Get car details.
     */
    public function show(string $id): JsonResponse
    {
        $car = Car::findOrFail($id);

        return response()->json([
            'car' => $car,
        ]);
    }

    /**
     * Check car availability for date range.
     */
    public function checkAvailability(Request $request, BookingService $bookingService): JsonResponse
    {
        $request->validate([
            'car_id' => ['required', 'uuid', 'exists:cars,id'],
            'start_date' => ['required', 'date'],
            'end_date' => ['required', 'date', 'after_or_equal:start_date'],
        ]);

        $available = $bookingService->checkAvailability(
            $request->car_id,
            Carbon::parse($request->start_date),
            Carbon::parse($request->end_date)
        );

        return response()->json([
            'available' => $available,
        ]);
    }

    /**
     * Create a new car (Admin only).
     */
    public function store(StoreCarRequest $request): JsonResponse
    {
        $data = $request->validated();

        // Handle thumbnail upload
        if ($request->hasFile('thumbnail')) {
            $path = $request->file('thumbnail')->store('cars', 'public');
            $data['thumbnail_url'] = Storage::url($path);
        }

        unset($data['thumbnail']);

        $car = Car::create($data);

        return response()->json([
            'message' => 'Mobil berhasil ditambahkan.',
            'car' => $car,
        ], 201);
    }

    /**
     * Update a car (Admin only).
     */
    public function update(UpdateCarRequest $request, string $id): JsonResponse
    {
        $car = Car::findOrFail($id);
        $data = $request->validated();

        // Handle thumbnail upload
        if ($request->hasFile('thumbnail')) {
            // Delete old thumbnail if exists
            if ($car->thumbnail_url) {
                $oldPath = str_replace('/storage/', '', $car->thumbnail_url);
                Storage::disk('public')->delete($oldPath);
            }

            $path = $request->file('thumbnail')->store('cars', 'public');
            $data['thumbnail_url'] = Storage::url($path);
        }

        unset($data['thumbnail']);

        $car->update($data);

        return response()->json([
            'message' => 'Mobil berhasil diperbarui.',
            'car' => $car->fresh(),
        ]);
    }

    /**
     * Delete a car (Admin only).
     */
    public function destroy(string $id): JsonResponse
    {
        $car = Car::findOrFail($id);

        // Soft delete by setting is_active to false
        $car->update(['is_active' => false]);

        return response()->json([
            'message' => 'Mobil berhasil dinonaktifkan.',
        ]);
    }
}
