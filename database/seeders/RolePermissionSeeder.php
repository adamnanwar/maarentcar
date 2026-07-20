<?php

namespace Database\Seeders;

use App\Models\Permission;
use App\Models\Role;
use Illuminate\Database\Seeder;

class RolePermissionSeeder extends Seeder
{
    public function run(): void
    {
        $roles = [
            ['name' => Role::ADMIN, 'label' => 'Administrator'],
            ['name' => Role::STAFF, 'label' => 'Staff Operasional'],
            ['name' => Role::CUSTOMER, 'label' => 'Pelanggan'],
        ];

        foreach ($roles as $role) {
            Role::updateOrCreate(['name' => $role['name']], $role);
        }

        $permissions = [
            ['module' => 'vehicle_categories', 'action' => 'view', 'slug' => 'vehicle_categories.view', 'label' => 'Lihat kategori mobil'],
            ['module' => 'vehicle_categories', 'action' => 'create', 'slug' => 'vehicle_categories.create', 'label' => 'Tambah kategori mobil'],
            ['module' => 'vehicle_categories', 'action' => 'update', 'slug' => 'vehicle_categories.update', 'label' => 'Ubah kategori mobil'],
            ['module' => 'vehicle_categories', 'action' => 'delete', 'slug' => 'vehicle_categories.delete', 'label' => 'Hapus kategori mobil'],

            ['module' => 'vehicles', 'action' => 'view', 'slug' => 'vehicles.view', 'label' => 'Lihat data mobil'],
            ['module' => 'vehicles', 'action' => 'create', 'slug' => 'vehicles.create', 'label' => 'Tambah data mobil'],
            ['module' => 'vehicles', 'action' => 'update', 'slug' => 'vehicles.update', 'label' => 'Ubah data mobil'],
            ['module' => 'vehicles', 'action' => 'delete', 'slug' => 'vehicles.delete', 'label' => 'Hapus data mobil'],

            ['module' => 'destinations', 'action' => 'view', 'slug' => 'destinations.view', 'label' => 'Lihat destinasi wisata'],
            ['module' => 'destinations', 'action' => 'create', 'slug' => 'destinations.create', 'label' => 'Tambah destinasi wisata'],
            ['module' => 'destinations', 'action' => 'update', 'slug' => 'destinations.update', 'label' => 'Ubah destinasi wisata'],
            ['module' => 'destinations', 'action' => 'delete', 'slug' => 'destinations.delete', 'label' => 'Hapus destinasi wisata'],

            ['module' => 'tour_packages', 'action' => 'view', 'slug' => 'tour_packages.view', 'label' => 'Lihat paket wisata'],
            ['module' => 'tour_packages', 'action' => 'create', 'slug' => 'tour_packages.create', 'label' => 'Tambah paket wisata'],
            ['module' => 'tour_packages', 'action' => 'update', 'slug' => 'tour_packages.update', 'label' => 'Ubah paket wisata'],
            ['module' => 'tour_packages', 'action' => 'delete', 'slug' => 'tour_packages.delete', 'label' => 'Hapus paket wisata'],

            ['module' => 'bookings', 'action' => 'view', 'slug' => 'bookings.view', 'label' => 'Lihat booking'],
            ['module' => 'bookings', 'action' => 'update_status', 'slug' => 'bookings.update_status', 'label' => 'Ubah status booking'],

            ['module' => 'payments', 'action' => 'verify', 'slug' => 'payments.verify', 'label' => 'Verifikasi pembayaran'],

            ['module' => 'reviews', 'action' => 'moderate', 'slug' => 'reviews.moderate', 'label' => 'Moderasi ulasan'],

            ['module' => 'users', 'action' => 'manage', 'slug' => 'users.manage', 'label' => 'Kelola akun customer'],
            ['module' => 'staff', 'action' => 'manage', 'slug' => 'staff.manage', 'label' => 'Kelola akun staff'],
            ['module' => 'reports', 'action' => 'view_full', 'slug' => 'reports.view_full', 'label' => 'Lihat laporan penuh'],
            ['module' => 'settings', 'action' => 'manage', 'slug' => 'settings.manage', 'label' => 'Kelola pengaturan sistem'],
        ];

        foreach ($permissions as $permission) {
            Permission::updateOrCreate(['slug' => $permission['slug']], $permission);
        }

        $adminRole = Role::where('name', Role::ADMIN)->first();
        $adminRole->permissions()->sync(Permission::pluck('id'));

        $staffRole = Role::where('name', Role::STAFF)->first();
        $staffPermissionSlugs = [
            'vehicle_categories.view',
            'vehicles.view',
            'vehicles.update',
            'destinations.view',
            'tour_packages.view',
            'bookings.view',
            'bookings.update_status',
            'payments.verify',
        ];
        $staffRole->permissions()->sync(Permission::whereIn('slug', $staffPermissionSlugs)->pluck('id'));

        $customerRole = Role::where('name', Role::CUSTOMER)->first();
        $customerRole->permissions()->sync([]);
    }
}
