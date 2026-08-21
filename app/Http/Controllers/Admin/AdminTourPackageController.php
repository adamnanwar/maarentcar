<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Admin\Concerns\DeletesPublicStorageFile;
use App\Http\Controllers\Controller;
use App\Models\Destination;
use App\Models\TourPackage;
use App\Models\Vehicle;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Inertia\Inertia;
use Inertia\Response;

class AdminTourPackageController extends Controller implements HasMiddleware
{
    use DeletesPublicStorageFile;

    public static function middleware(): array
    {
        return [
            new Middleware('permission:tour_packages.create', only: ['create', 'store']),
            new Middleware('permission:tour_packages.update', only: ['edit', 'update']),
            new Middleware('permission:tour_packages.delete', only: ['destroy']),
        ];
    }

    public function index(Request $request): Response
    {
        $query = TourPackage::query()->with('destinations');

        if ($search = $request->string('search')->trim()->value()) {
            $query->where('name', 'ilike', "%{$search}%");
        }

        return Inertia::render('Admin/PaketWisata/Index', [
            'packages' => $query->latest()->paginate(15)->withQueryString(),
            'filters' => $request->only('search'),
        ]);
    }

    public function create(): Response
    {
        return Inertia::render('Admin/PaketWisata/Create', [
            'vehicles' => Vehicle::orderBy('name')->get(['id', 'name']),
            'destinations' => Destination::orderBy('name')->get(['id', 'name']),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $this->validated($request);
        $destinationIds = $data['destination_ids'] ?? [];
        unset($data['destination_ids']);

        if ($request->hasFile('image')) {
            $data['image_path'] = Storage::disk('public')->url(
                $request->file('image')->store('tour-packages', 'public')
            );
        }

        $package = TourPackage::create($data);
        $this->syncDestinations($package, $destinationIds);

        return redirect()->route('admin.paket-wisata.index')->with('success', 'Paket wisata berhasil ditambahkan.');
    }

    public function edit(TourPackage $paket_wisata): Response
    {
        $paket_wisata->load('destinations');

        return Inertia::render('Admin/PaketWisata/Edit', [
            'package' => $paket_wisata,
            'vehicles' => Vehicle::orderBy('name')->get(['id', 'name']),
            'destinations' => Destination::orderBy('name')->get(['id', 'name']),
        ]);
    }

    public function update(Request $request, TourPackage $paket_wisata): RedirectResponse
    {
        $data = $this->validated($request, $paket_wisata->id);
        $destinationIds = $data['destination_ids'] ?? [];
        unset($data['destination_ids']);

        if ($request->hasFile('image')) {
            $this->deletePublicFile($paket_wisata->image_path);

            $data['image_path'] = Storage::disk('public')->url(
                $request->file('image')->store('tour-packages', 'public')
            );
        }

        $paket_wisata->update($data);
        $this->syncDestinations($paket_wisata, $destinationIds);

        return redirect()->route('admin.paket-wisata.index')->with('success', 'Paket wisata berhasil diperbarui.');
    }

    public function destroy(TourPackage $paket_wisata): RedirectResponse
    {
        $paket_wisata->delete();

        return back()->with('success', 'Paket wisata berhasil dihapus.');
    }

    private function syncDestinations(TourPackage $package, array $destinationIds): void
    {
        $sync = [];
        foreach (array_values($destinationIds) as $order => $destinationId) {
            $sync[$destinationId] = ['sort_order' => $order];
        }

        $package->destinations()->sync($sync);
    }

    private function validated(Request $request, ?int $ignoreId = null): array
    {
        return $request->validate([
            'vehicle_id' => ['nullable', 'exists:vehicles,id'],
            'name' => ['required', 'string', 'max:150'],
            'slug' => ['nullable', 'string', 'max:170', Rule::unique('tour_packages', 'slug')->ignore($ignoreId)],
            'seat_capacity' => ['required', 'integer', 'min:1', 'max:50'],
            'duration_days' => ['required', 'integer', 'min:1'],
            'driver_included' => ['boolean'],
            'price' => ['required', 'numeric', 'min:0'],
            'description' => ['nullable', 'string'],
            'is_active' => ['boolean'],
            'image' => ['nullable', 'image', 'max:4096'],
            'destination_ids' => ['nullable', 'array'],
            'destination_ids.*' => ['exists:destinations,id'],
        ]);
    }
}
