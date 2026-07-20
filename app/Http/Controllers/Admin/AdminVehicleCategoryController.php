<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\VehicleCategory;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Inertia\Inertia;
use Inertia\Response;

class AdminVehicleCategoryController extends Controller implements HasMiddleware
{
    public static function middleware(): array
    {
        return [
            new Middleware('permission:vehicle_categories.create', only: ['create', 'store']),
            new Middleware('permission:vehicle_categories.update', only: ['edit', 'update']),
            new Middleware('permission:vehicle_categories.delete', only: ['destroy']),
        ];
    }

    public function index(Request $request): Response
    {
        $query = VehicleCategory::query()->withCount('vehicles');

        if ($search = $request->string('search')->trim()->value()) {
            $query->where('name', 'ilike', "%{$search}%");
        }

        return Inertia::render('Admin/KategoriMobil/Index', [
            'categories' => $query->orderBy('name')->paginate(15)->withQueryString(),
            'filters' => $request->only('search'),
        ]);
    }

    public function create(): Response
    {
        return Inertia::render('Admin/KategoriMobil/Create');
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $this->validated($request);

        if ($request->hasFile('icon')) {
            $data['icon_path'] = Storage::disk('public')->url(
                $request->file('icon')->store('vehicle-categories', 'public')
            );
        }

        VehicleCategory::create($data);

        return redirect()->route('admin.kategori-mobil.index')->with('success', 'Kategori mobil berhasil ditambahkan.');
    }

    public function edit(VehicleCategory $kategori_mobil): Response
    {
        return Inertia::render('Admin/KategoriMobil/Edit', ['category' => $kategori_mobil]);
    }

    public function update(Request $request, VehicleCategory $kategori_mobil): RedirectResponse
    {
        $data = $this->validated($request, $kategori_mobil->id);

        if ($request->hasFile('icon')) {
            $data['icon_path'] = Storage::disk('public')->url(
                $request->file('icon')->store('vehicle-categories', 'public')
            );
        }

        $kategori_mobil->update($data);

        return redirect()->route('admin.kategori-mobil.index')->with('success', 'Kategori mobil berhasil diperbarui.');
    }

    public function destroy(VehicleCategory $kategori_mobil): RedirectResponse
    {
        $kategori_mobil->delete();

        return back()->with('success', 'Kategori mobil berhasil dihapus.');
    }

    private function validated(Request $request, ?int $ignoreId = null): array
    {
        return $request->validate([
            'name' => ['required', 'string', 'max:100'],
            'slug' => ['nullable', 'string', 'max:120', Rule::unique('vehicle_categories', 'slug')->ignore($ignoreId)],
            'description' => ['nullable', 'string'],
            'icon' => ['nullable', 'image', 'max:2048'],
        ]);
    }
}
