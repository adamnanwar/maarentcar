<?php

use App\Models\Booking;
use App\Models\Payment;
use App\Models\Role;
use App\Models\TourPackage;
use App\Models\User;
use App\Models\Vehicle;
use App\Models\VehicleCategory;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

beforeEach(function () {
    $this->seed(RolePermissionSeeder::class);
    Storage::fake('local');

    $this->customer = User::factory()->create(['role_id' => Role::where('name', Role::CUSTOMER)->value('id')]);
    $this->admin = User::factory()->create(['role_id' => Role::where('name', Role::ADMIN)->value('id')]);

    $category = VehicleCategory::create(['name' => 'MPV']);
    $this->vehicle = Vehicle::create([
        'category_id' => $category->id,
        'name' => 'Toyota Avanza',
        'plate_number' => 'BP 1234 XX',
        'seat_capacity' => 7,
        'price_per_day' => 300000,
        'driver_fee_per_day' => 150000,
        'base_delivery_fee' => 50000,
    ]);
});

test('customer can create a self-drive vehicle booking with correct price', function () {
    $response = $this->actingAs($this->customer)->post('/booking', [
        'booking_type' => 'mobil',
        'vehicle_id' => $this->vehicle->id,
        'start_datetime' => now()->addDay()->format('Y-m-d\TH:i'),
        'end_datetime' => now()->addDays(3)->format('Y-m-d\TH:i'),
        'with_driver' => false,
        'delivery_method' => 'pickup_at_office',
        'ktp_photo' => UploadedFile::fake()->create('ktp.jpg', 100, 'image/jpeg'),
    ]);

    $booking = Booking::first();
    $response->assertRedirect(route('booking.show', $booking));

    expect($booking->booking_type)->toBe('mobil');
    expect($booking->status)->toBe('menunggu_pembayaran');
    expect((float) $booking->base_price)->toBe(600000.0); // 2 days * 300000
    expect((float) $booking->driver_fee)->toBe(0.0);
    expect((float) $booking->total_price)->toBe(600000.0);
});

test('vehicle booking with driver and delivery adds driver fee and delivery fee', function () {
    $this->actingAs($this->customer)->post('/booking', [
        'booking_type' => 'mobil',
        'vehicle_id' => $this->vehicle->id,
        'start_datetime' => now()->addDay()->format('Y-m-d\TH:i'),
        'end_datetime' => now()->addDays(2)->format('Y-m-d\TH:i'),
        'with_driver' => true,
        'delivery_method' => 'driver_pickup',
        'recipient_name' => 'Budi',
        'address_phone' => '08123456789',
        'full_address' => 'Jl. Contoh No. 1',
        'ktp_photo' => UploadedFile::fake()->create('ktp.jpg', 100, 'image/jpeg'),
    ]);

    $booking = Booking::first();

    expect((float) $booking->base_price)->toBe(300000.0);
    expect((float) $booking->driver_fee)->toBe(150000.0);
    expect((float) $booking->total_price)->toBe(450000.0);
    expect($booking->pickup_address_snapshot['recipient_name'])->toBe('Budi');
});

test('double booking on overlapping dates is rejected', function () {
    Booking::create([
        'booking_code' => 'WRC-TEST-0001',
        'user_id' => $this->customer->id,
        'booking_type' => 'mobil',
        'vehicle_id' => $this->vehicle->id,
        'start_datetime' => now()->addDays(2),
        'end_datetime' => now()->addDays(4),
        'duration_days' => 2,
        'with_driver' => false,
        'delivery_method' => 'pickup_at_office',
        'total_price' => 600000,
        'status' => 'dikonfirmasi',
    ]);

    // Pelanggan lain, supaya aturan "1 booking mobil aktif per pelanggan" tidak ikut memblokir
    // permintaan ini - yang ingin diuji di sini murni validasi tanggal bentrok.
    $otherCustomer = User::factory()->create(['role_id' => Role::where('name', Role::CUSTOMER)->value('id')]);

    $response = $this->actingAs($otherCustomer)->post('/booking', [
        'booking_type' => 'mobil',
        'vehicle_id' => $this->vehicle->id,
        'start_datetime' => now()->addDays(3)->format('Y-m-d\TH:i'),
        'end_datetime' => now()->addDays(5)->format('Y-m-d\TH:i'),
        'with_driver' => false,
        'delivery_method' => 'pickup_at_office',
        'ktp_photo' => UploadedFile::fake()->create('ktp.jpg', 100, 'image/jpeg'),
    ]);

    $response->assertSessionHasErrors('start_datetime');
    expect(Booking::count())->toBe(1);
});

test('customer can create a fixed-price package booking', function () {
    $package = TourPackage::create([
        'name' => 'Batam 1 Hari',
        'seat_capacity' => 6,
        'duration_days' => 1,
        'price' => 850000,
    ]);

    $this->actingAs($this->customer)->post('/booking', [
        'booking_type' => 'paket_wisata',
        'package_id' => $package->id,
        'start_datetime' => now()->addDay()->format('Y-m-d'),
        'recipient_name' => 'Budi',
        'address_phone' => '08123456789',
        'full_address' => 'Jl. Contoh No. 1',
        'delivery_method' => 'driver_pickup',
    ]);

    $booking = Booking::first();
    expect($booking->booking_type)->toBe('paket_wisata');
    expect((float) $booking->total_price)->toBe(850000.0);
    expect($booking->with_driver)->toBeTrue();
});

test('full payment verification flow: upload proof then admin verifies', function () {
    $booking = Booking::create([
        'booking_code' => 'WRC-TEST-0002',
        'user_id' => $this->customer->id,
        'booking_type' => 'mobil',
        'vehicle_id' => $this->vehicle->id,
        'start_datetime' => now()->addDay(),
        'end_datetime' => now()->addDays(2),
        'duration_days' => 1,
        'with_driver' => false,
        'delivery_method' => 'pickup_at_office',
        'total_price' => 300000,
        'status' => 'menunggu_pembayaran',
    ]);

    $this->actingAs($this->customer)->post("/booking/{$booking->id}/pembayaran", [
        'proof' => UploadedFile::fake()->create('bukti.jpg', 100, 'image/jpeg'),
    ])->assertRedirect();

    $booking->refresh();
    expect($booking->status)->toBe('menunggu_verifikasi');

    $payment = $booking->payments()->first();
    expect($payment->status)->toBe('menunggu');

    $this->actingAs($this->admin)->post("/admin/pembayaran/{$payment->id}/verifikasi")->assertRedirect();

    $booking->refresh();
    $payment->refresh();
    expect($booking->status)->toBe('dikonfirmasi');
    expect($payment->status)->toBe('terverifikasi');
});

test('admin rejecting a payment sends the booking back to menunggu_pembayaran', function () {
    $booking = Booking::create([
        'booking_code' => 'WRC-TEST-0003',
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

    $payment = $booking->payments()->create([
        'amount' => 300000,
        'proof_path' => 'payment-proofs/1/fake.jpg',
        'status' => Payment::STATUS_MENUNGGU,
    ]);

    $this->actingAs($this->admin)
        ->post("/admin/pembayaran/{$payment->id}/tolak", ['reason' => 'Nominal tidak sesuai'])
        ->assertRedirect();

    $booking->refresh();
    $payment->refresh();
    expect($booking->status)->toBe('menunggu_pembayaran');
    expect($payment->status)->toBe('ditolak');
    expect($payment->verifications()->first()->reason)->toBe('Nominal tidak sesuai');
});

test('customer cannot access admin payment verification routes', function () {
    $this->actingAs($this->customer)->get('/admin/pembayaran')->assertRedirect(route('home'));
});

test('ktp photo is required to create a vehicle booking', function () {
    $response = $this->actingAs($this->customer)->post('/booking', [
        'booking_type' => 'mobil',
        'vehicle_id' => $this->vehicle->id,
        'start_datetime' => now()->addDay()->format('Y-m-d\TH:i'),
        'end_datetime' => now()->addDays(2)->format('Y-m-d\TH:i'),
        'with_driver' => false,
        'delivery_method' => 'pickup_at_office',
    ]);

    $response->assertSessionHasErrors('ktp_photo');
    expect(Booking::count())->toBe(0);
});

test('customer with an active vehicle booking is blocked from creating a second one', function () {
    Booking::create([
        'booking_code' => 'WRC-TEST-0004',
        'user_id' => $this->customer->id,
        'booking_type' => 'mobil',
        'vehicle_id' => $this->vehicle->id,
        'start_datetime' => now()->addDays(5),
        'end_datetime' => now()->addDays(6),
        'duration_days' => 1,
        'with_driver' => false,
        'delivery_method' => 'pickup_at_office',
        'total_price' => 300000,
        'status' => 'menunggu_pembayaran',
    ]);

    $response = $this->actingAs($this->customer)->get("/booking/mobil/{$this->vehicle->slug}/baru");

    $response->assertRedirect();
    expect(session('error'))->not->toBeNull();
    expect(Booking::count())->toBe(1);
});

test('unpaid vehicle booking is cancelled automatically after the 1 hour payment window', function () {
    $booking = Booking::create([
        'booking_code' => 'WRC-TEST-0005',
        'user_id' => $this->customer->id,
        'booking_type' => 'mobil',
        'vehicle_id' => $this->vehicle->id,
        'start_datetime' => now()->addDay(),
        'end_datetime' => now()->addDays(2),
        'duration_days' => 1,
        'with_driver' => false,
        'delivery_method' => 'pickup_at_office',
        'total_price' => 300000,
        'status' => 'menunggu_pembayaran',
        'payment_due_at' => now()->subMinute(),
    ]);

    $this->actingAs($this->customer)->get("/booking/{$booking->id}");

    expect($booking->refresh()->status)->toBe('dibatalkan');
});

test('a freshly created booking payment deadline is genuinely about 1 hour in the future', function () {
    // Regresi: jika koneksi database tidak dipaksa UTC (lihat config/database.php
    // pgsql.timezone), PostgreSQL bisa menginterpretasikan timestamp yang dikirim
    // Laravel memakai timezone sesi server/OS-nya sendiri (mis. Asia/Bangkok),
    // sehingga payment_due_at yang seharusnya "1 jam ke depan" malah tersimpan
    // beberapa jam ke BELAKANG dan booking langsung dianggap kedaluwarsa.
    $this->actingAs($this->customer)->post('/booking', [
        'booking_type' => 'mobil',
        'vehicle_id' => $this->vehicle->id,
        'start_datetime' => now()->addDay()->format('Y-m-d\TH:i'),
        'end_datetime' => now()->addDays(2)->format('Y-m-d\TH:i'),
        'with_driver' => false,
        'delivery_method' => 'pickup_at_office',
        'ktp_photo' => UploadedFile::fake()->create('ktp.jpg', 100, 'image/jpeg'),
    ]);

    $booking = Booking::firstOrFail();

    expect($booking->status)->toBe('menunggu_pembayaran');
    expect($booking->payment_due_at->isFuture())->toBeTrue();
    expect(now()->diffInMinutes($booking->payment_due_at, false))->toBeGreaterThan(55)
        ->toBeLessThan(65);
});
