<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Permission;
use App\Models\Role;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Inertia\Inertia;
use Inertia\Response;

class AdminStaffController extends Controller implements HasMiddleware
{
    public static function middleware(): array
    {
        return [
            new Middleware('permission:staff.manage'),
        ];
    }

    public function index(Request $request): Response
    {
        $query = User::query()->whereHas('role', fn ($q) => $q->where('name', Role::STAFF));

        if ($search = $request->string('search')->trim()->value()) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'ilike', "%{$search}%")->orWhere('email', 'ilike', "%{$search}%");
            });
        }

        if ($status = $request->string('status')->value()) {
            $query->where('status', $status);
        }

        $staffRole = Role::where('name', Role::STAFF)->first();

        return Inertia::render('Admin/Staff/Index', [
            'staff' => $query->latest()->paginate(15)->withQueryString(),
            'filters' => $request->only(['search', 'status']),
            'permissions' => Permission::orderBy('module')->orderBy('action')->get()->groupBy('module'),
            'staffPermissionIds' => $staffRole->permissions()->pluck('permissions.id'),
        ]);
    }

    public function create(): Response
    {
        return Inertia::render('Admin/Staff/Create');
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $this->validated($request);

        User::create([
            'role_id' => Role::where('name', Role::STAFF)->value('id'),
            'name' => $data['name'],
            'email' => $data['email'],
            'phone' => $data['phone'] ?? null,
            'password' => Hash::make($data['password']),
        ]);

        return redirect()->route('admin.staff.index')->with('success', 'Akun staff berhasil ditambahkan.');
    }

    public function edit(User $staff): Response
    {
        return Inertia::render('Admin/Staff/Edit', ['staff' => $staff]);
    }

    public function update(Request $request, User $staff): RedirectResponse
    {
        $data = $this->validated($request, $staff->id, passwordRequired: false);

        $staff->update([
            'name' => $data['name'],
            'email' => $data['email'],
            'phone' => $data['phone'] ?? null,
            ...(($data['password'] ?? null) ? ['password' => Hash::make($data['password'])] : []),
        ]);

        return redirect()->route('admin.staff.index')->with('success', 'Akun staff berhasil diperbarui.');
    }

    public function toggleStatus(User $staff): RedirectResponse
    {
        $staff->update([
            'status' => $staff->status === User::STATUS_ACTIVE ? User::STATUS_INACTIVE : User::STATUS_ACTIVE,
        ]);

        return back()->with('success', 'Status staff berhasil diperbarui.');
    }

    public function destroy(User $staff): RedirectResponse
    {
        $staff->delete();

        return redirect()->route('admin.staff.index')->with('success', 'Akun staff berhasil dihapus.');
    }

    public function updateRolePermissions(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'permission_ids' => ['array'],
            'permission_ids.*' => ['integer', 'exists:permissions,id'],
        ]);

        Role::where('name', Role::STAFF)->first()->permissions()->sync($data['permission_ids'] ?? []);

        return back()->with('success', 'Permission staff berhasil diperbarui.');
    }

    private function validated(Request $request, ?int $ignoreId = null, bool $passwordRequired = true): array
    {
        return $request->validate([
            'name' => ['required', 'string', 'max:150'],
            'email' => ['required', 'email', 'max:150', Rule::unique('users', 'email')->ignore($ignoreId)],
            'phone' => ['nullable', 'string', 'max:30'],
            'password' => [$passwordRequired ? 'required' : 'nullable', 'string', 'min:8'],
        ]);
    }
}
