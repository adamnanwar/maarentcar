<?php

use App\Models\Booking;
use App\Models\Payment;
use App\Models\Role;
use App\Models\User;
use App\Models\Vehicle;
use App\Models\VehicleCategory;
use Database\Seeders\RolePermissionSeeder;
use Inertia\Testing\AssertableInertia as Assert;

beforeEach(function () {
    $this->seed(RolePermissionSeeder::class);

    $this->staff = User::factory()->create(['role_id' => Role::where('name', Role::STAFF)->value('id')]);
    $this->admin = User::factory()->create(['role_id' => Role::where('name', Role::ADMIN)->value('id')]);
    $this->customer = User::factory()->create(['role_id' => Role::where('name', Role::CUSTOMER)->value('id')]);

    $category = VehicleCategory::create(['name' => 'MPV']);
    $this->vehicle = Vehicle::create([
        'category_id' => $category->id,
        'name' => 'Toyota Avanza',
        'plate_number' => 'BP 1234 XX',
        'seat_capacity' => 7,
        'price_per_day' => 300000,
    ]);

    $this->booking = Booking::create([
        'booking_code' => 'WRC-TEST-REPORT',
        'user_id' => $this->customer->id,
        'booking_type' => 'mobil',
        'vehicle_id' => $this->vehicle->id,
        'start_datetime' => now()->subDays(3),
        'end_datetime' => now()->subDay(),
        'duration_days' => 2,
        'delivery_method' => 'pickup_at_office',
        'total_price' => 600000,
        'status' => 'dikonfirmasi',
    ]);

    $this->booking->payments()->create([
        'amount' => 600000,
        'proof_path' => 'payment-proofs/1/fake.jpg',
        'status' => Payment::STATUS_TERVERIFIKASI,
    ]);
});

test('staff is blocked from the report page', function () {
    $this->actingAs($this->staff)->get('/admin/laporan')->assertForbidden();
});

test('admin sees revenue that matches verified payments', function () {
    $this->actingAs($this->admin)
        ->get('/admin/laporan')
        ->assertInertia(fn (Assert $page) => $page
            ->component('Admin/Laporan/Index')
            ->where('stats.total_revenue', 600000)
            ->where('stats.total_bookings', 1)
        );
});

test('admin can export the report as csv', function () {
    $response = $this->actingAs($this->admin)->get('/admin/laporan/ekspor');

    $response->assertOk();
    $response->assertHeader('content-type', 'text/csv; charset=utf-8');
    expect($response->streamedContent())->toContain('Tanggal,Pendapatan');
});
