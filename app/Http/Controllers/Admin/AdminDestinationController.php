<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Admin\Concerns\DeletesPublicStorageFile;
use App\Http\Controllers\Controller;
use App\Models\Destination;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Inertia\Inertia;
use Inertia\Response;

class AdminDestinationController extends Controller implements HasMiddleware
{
    use DeletesPublicStorageFile;

    public static function middleware(): array
    {
        return [
            new Middleware('permission:destinations.create', only: ['create', 'store']),
            new Middleware('permission:destinations.update', only: ['edit', 'update']),
            new Middleware('permission:destinations.delete', only: ['destroy']),
        ];
    }

    public function index(Request $request): Response
    {
        $query = Destination::query();

        if ($search = $request->string('search')->trim()->value()) {
            $query->where('name', 'ilike', "%{$search}%");
        }

        return Inertia::render('Admin/Destinasi/Index', [
            'destinations' => $query->orderBy('name')->paginate(15)->withQueryString(),
            'filters' => $request->only('search'),
        ]);
    }

    public function create(): Response
    {
        return Inertia::render('Admin/Destinasi/Create');
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $this->validated($request);

        if ($request->hasFile('image')) {
            $data['image_path'] = Storage::disk('public')->url(
                $request->file('image')->store('destinations', 'public')
            );
        }

        Destination::create($data);

        return redirect()->route('admin.destinasi.index')->with('success', 'Destinasi berhasil ditambahkan.');
    }

    public function edit(Destination $destinasi): Response
    {
        return Inertia::render('Admin/Destinasi/Edit', ['destination' => $destinasi]);
    }

    public function update(Request $request, Destination $destinasi): RedirectResponse
    {
        $data = $this->validated($request, $destinasi->id);

        if ($request->hasFile('image')) {
            $this->deletePublicFile($destinasi->image_path);

            $data['image_path'] = Storage::disk('public')->url(
                $request->file('image')->store('destinations', 'public')
            );
        }

        $destinasi->update($data);

        return redirect()->route('admin.destinasi.index')->with('success', 'Destinasi berhasil diperbarui.');
    }

    public function destroy(Destination $destinasi): RedirectResponse
    {
        $destinasi->delete();

        return back()->with('success', 'Destinasi berhasil dihapus.');
    }

    private function validated(Request $request, ?int $ignoreId = null): array
    {
        return $request->validate([
            'name' => ['required', 'string', 'max:150'],
            'slug' => ['nullable', 'string', 'max:170', Rule::unique('destinations', 'slug')->ignore($ignoreId)],
            'category' => ['nullable', 'string', 'max:50'],
            'description' => ['nullable', 'string'],
            'address' => ['nullable', 'string'],
            'addon_price' => ['nullable', 'numeric', 'min:0'],
            'is_active' => ['boolean'],
            'image' => ['nullable', 'image', 'max:4096'],
        ]);
    }
}
