<?php

use App\Models\Booking;
use App\Models\Payment;
use App\Models\Role;
use App\Models\TourPackage;
use App\Models\User;
use App\Models\Vehicle;
use App\Models\VehicleCategory;
use Database\Seeders\RolePermissionSeeder;
use Inertia\Testing\AssertableInertia as Assert;

beforeEach(function () {
    $this->seed(RolePermissionSeeder::class);

    $this->customer = User::factory()->create(['role_id' => Role::where('name', Role::CUSTOMER)->value('id')]);
    $this->admin = User::factory()->create(['role_id' => Role::where('name', Role::ADMIN)->value('id')]);

    $category = VehicleCategory::create(['name' => 'MPV']);
    $this->vehicle = Vehicle::create([
        'category_id' => $category->id,
        'name' => 'Toyota Avanza',
        'plate_number' => 'BP 1234 XX',
        'seat_capacity' => 7,
        'price_per_day' => 300000,
    ]);
    $this->package = TourPackage::create(['name' => 'Batam 1 Hari', 'seat_capacity' => 6, 'duration_days' => 1, 'price' => 850000]);
    $this->booking = Booking::create([
        'booking_code' => 'WRC-TEST-0001',
        'user_id' => $this->customer->id,
        'booking_type' => 'mobil',
        'vehicle_id' => $this->vehicle->id,
        'start_datetime' => now()->addDay(),
        'end_datetime' => now()->addDays(2),
        'duration_days' => 1,
        'with_driver' => false,
        'delivery_method' => 'pickup_at_office',
        'total_price' => 300000,
        'status' => 'menunggu_verifikasi',
    ]);
    $this->payment = $this->booking->payments()->create([
        'amount' => 300000,
        'proof_path' => 'payment-proofs/1/fake.jpg',
        'status' => Payment::STATUS_MENUNGGU,
    ]);
});

test('booking wizard pages render the correct Inertia component', function () {
    // Pelanggan baru tanpa booking mobil aktif, supaya aturan "1 booking mobil aktif
    // per pelanggan" tidak menghalangi halaman wizard ini untuk dibuka.
    $customerWithoutActiveBooking = User::factory()->create(['role_id' => Role::where('name', Role::CUSTOMER)->value('id')]);

    $this->actingAs($customerWithoutActiveBooking)
        ->get("/booking/mobil/{$this->vehicle->slug}/baru")
        ->assertInertia(fn (Assert $page) => $page->component('Booking/CreateMobil'));

    $this->actingAs($this->customer)
        ->get("/booking/paket-wisata/{$this->package->slug}/baru")
        ->assertInertia(fn (Assert $page) => $page->component('Booking/CreatePaket'));

    $this->actingAs($this->customer)
        ->get('/booking')
        ->assertInertia(fn (Assert $page) => $page->component('Booking/Index'));

    $this->actingAs($this->customer)
        ->get("/booking/{$this->booking->id}")
        ->assertInertia(fn (Assert $page) => $page->component('Booking/Show'));
});

test('admin booking and payment pages render the correct Inertia component', function () {
    $this->actingAs($this->admin)
        ->get('/admin/booking')
        ->assertInertia(fn (Assert $page) => $page->component('Admin/Booking/Index'));

    $this->actingAs($this->admin)
        ->get("/admin/booking/{$this->booking->id}")
        ->assertInertia(fn (Assert $page) => $page->component('Admin/Booking/Show'));

    $this->actingAs($this->admin)
        ->get('/admin/pembayaran')
        ->assertInertia(fn (Assert $page) => $page->component('Admin/Pembayaran/Index'));
});
