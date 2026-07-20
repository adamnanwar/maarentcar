<?php

namespace Database\Seeders;

use App\Models\Role;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        $adminRoleId = Role::where('name', Role::ADMIN)->value('id');
        $staffRoleId = Role::where('name', Role::STAFF)->value('id');
        $customerRoleId = Role::where('name', Role::CUSTOMER)->value('id');

        User::updateOrCreate(
            ['email' => 'admin@werentcar.com'],
            [
                'role_id' => $adminRoleId,
                'name' => 'Admin We Rent Car',
                'phone' => '081200000001',
                'password' => Hash::make('password'),
                'email_verified_at' => now(),
            ]
        );

        User::updateOrCreate(
            ['email' => 'staff@werentcar.com'],
            [
                'role_id' => $staffRoleId,
                'name' => 'Staff Operasional',
                'phone' => '081200000002',
                'password' => Hash::make('password'),
                'email_verified_at' => now(),
            ]
        );

        User::updateOrCreate(
            ['email' => 'customer@example.com'],
            [
                'role_id' => $customerRoleId,
                'name' => 'Budi Pelanggan',
                'phone' => '081200000003',
                'password' => Hash::make('password'),
                'email_verified_at' => now(),
            ]
        );
    }
}
