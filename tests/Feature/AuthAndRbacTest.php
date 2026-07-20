<?php

use App\Models\Role;
use App\Models\User;
use Database\Seeders\RolePermissionSeeder;

beforeEach(function () {
    $this->seed(RolePermissionSeeder::class);
});

test('guest can view the login page', function () {
    $this->get('/login')->assertStatus(200);
});

test('admin login redirects to admin dashboard', function () {
    $admin = User::factory()->create([
        'role_id' => Role::where('name', Role::ADMIN)->value('id'),
        'password' => bcrypt('password'),
    ]);

    $this->post('/login', [
        'email' => $admin->email,
        'password' => 'password',
    ])->assertRedirect(route('admin.dashboard'));
});

test('customer login redirects to customer dashboard', function () {
    $customer = User::factory()->create([
        'role_id' => Role::where('name', Role::CUSTOMER)->value('id'),
        'password' => bcrypt('password'),
    ]);

    $this->post('/login', [
        'email' => $customer->email,
        'password' => 'password',
    ])->assertRedirect(route('dashboard'));
});

test('staff can view vehicle category list but cannot create one', function () {
    $staff = User::factory()->create([
        'role_id' => Role::where('name', Role::STAFF)->value('id'),
    ]);

    $this->actingAs($staff)->get('/admin/kategori-mobil')->assertStatus(200);
    $this->actingAs($staff)->get('/admin/kategori-mobil/create')->assertForbidden();
    $this->actingAs($staff)->post('/admin/kategori-mobil', ['name' => 'SUV'])->assertForbidden();
});

test('admin can create a vehicle category', function () {
    $admin = User::factory()->create([
        'role_id' => Role::where('name', Role::ADMIN)->value('id'),
    ]);

    $this->actingAs($admin)
        ->post('/admin/kategori-mobil', ['name' => 'Sedan'])
        ->assertRedirect('/admin/kategori-mobil');

    $this->assertDatabaseHas('vehicle_categories', ['name' => 'Sedan', 'slug' => 'sedan']);
});

test('customer cannot access the admin area', function () {
    $customer = User::factory()->create([
        'role_id' => Role::where('name', Role::CUSTOMER)->value('id'),
    ]);

    $this->actingAs($customer)->get('/admin')->assertRedirect(route('home'));
});
