<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Vehicle;
use App\Models\VehicleCategory;
use App\Models\VehicleImage;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Inertia\Inertia;
use Inertia\Response;

class AdminVehicleController extends Controller
{
    public function index(Request $request): Response
    {
        $query = Vehicle::query()->with(['category', 'images']);

        if ($search = $request->string('search')->trim()->value()) {
            $query->where('name', 'ilike', "%{$search}%");
        }

        if ($status = $request->string('status')->value()) {
            $query->where('status', $status);
        }

        return Inertia::render('Admin/Mobil/Index', [
            'vehicles' => $query->latest()->paginate(15)->withQueryString(),
            'categories' => VehicleCategory::orderBy('name')->get(),
            'filters' => $request->only(['search', 'status']),
        ]);
    }

    public function create(): Response
    {
        return Inertia::render('Admin/Mobil/Create', [
            'categories' => VehicleCategory::orderBy('name')->get(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $this->validated($request);

        $vehicle = Vehicle::create($data);

        $this->storeImages($request, $vehicle);

        return redirect()->route('admin.mobil.index')->with('success', 'Mobil berhasil ditambahkan.');
    }

    public function edit(Vehicle $mobil): Response
    {
        $mobil->load('images');

        return Inertia::render('Admin/Mobil/Edit', [
            'vehicle' => $mobil,
            'categories' => VehicleCategory::orderBy('name')->get(),
        ]);
    }

    public function update(Request $request, Vehicle $mobil): RedirectResponse
    {
        $data = $this->validated($request, $mobil->id);

        $mobil->update($data);

        $this->storeImages($request, $mobil);

        return redirect()->route('admin.mobil.index')->with('success', 'Mobil berhasil diperbarui.');
    }

    public function destroy(Vehicle $mobil): RedirectResponse
    {
        $mobil->delete();

        return back()->with('success', 'Mobil berhasil dihapus.');
    }

    public function destroyImage(Vehicle $mobil, VehicleImage $image): RedirectResponse
    {
        abort_unless($image->vehicle_id === $mobil->id, 404);

        $image->delete();

        return back()->with('success', 'Foto berhasil dihapus.');
    }

    private function storeImages(Request $request, Vehicle $vehicle): void
    {
        if (! $request->hasFile('images')) {
            return;
        }

        $hasPrimary = $vehicle->images()->where('is_primary', true)->exists();

        foreach ($request->file('images') as $index => $file) {
            $path = Storage::disk('public')->url($file->store('vehicles', 'public'));

            VehicleImage::create([
                'vehicle_id' => $vehicle->id,
                'image_path' => $path,
                'is_primary' => ! $hasPrimary && $index === 0,
                'sort_order' => $vehicle->images()->max('sort_order') + 1,
            ]);
        }
    }

    private function validated(Request $request, ?int $ignoreId = null): array
    {
        return $request->validate([
            'category_id' => ['required', 'exists:vehicle_categories,id'],
            'name' => ['required', 'string', 'max:150'],
            'slug' => ['nullable', 'string', 'max:170', Rule::unique('vehicles', 'slug')->ignore($ignoreId)],
            'brand' => ['nullable', 'string', 'max:100'],
            'model' => ['nullable', 'string', 'max:100'],
            'year' => ['nullable', 'integer', 'min:1990', 'max:'.(date('Y') + 1)],
            'plate_number' => ['required', 'string', 'max:20', Rule::unique('vehicles', 'plate_number')->ignore($ignoreId)],
            'transmission' => ['required', Rule::in([Vehicle::TRANSMISSION_MANUAL, Vehicle::TRANSMISSION_AUTOMATIC])],
            'fuel_type' => ['required', 'string', 'max:20'],
            'seat_capacity' => ['required', 'integer', 'min:1', 'max:50'],
            'price_per_day' => ['required', 'numeric', 'min:0'],
            'driver_fee_per_day' => ['nullable', 'numeric', 'min:0'],
            'base_delivery_fee' => ['nullable', 'numeric', 'min:0'],
            'description' => ['nullable', 'string'],
            'status' => ['required', Rule::in([Vehicle::STATUS_TERSEDIA, Vehicle::STATUS_PERAWATAN, Vehicle::STATUS_NONAKTIF])],
            'is_active' => ['boolean'],
            'images' => ['nullable', 'array'],
            'images.*' => ['image', 'max:4096'],
        ]);
    }
}
