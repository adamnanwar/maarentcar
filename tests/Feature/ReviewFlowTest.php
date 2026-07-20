<?php

use App\Models\Booking;
use App\Models\Review;
use App\Models\Role;
use App\Models\User;
use App\Models\Vehicle;
use App\Models\VehicleCategory;
use Database\Seeders\RolePermissionSeeder;
use Inertia\Testing\AssertableInertia as Assert;

beforeEach(function () {
    $this->seed(RolePermissionSeeder::class);

    $this->customer = User::factory()->create(['role_id' => Role::where('name', Role::CUSTOMER)->value('id')]);
    $this->otherCustomer = User::factory()->create(['role_id' => Role::where('name', Role::CUSTOMER)->value('id')]);
    $this->staff = User::factory()->create(['role_id' => Role::where('name', Role::STAFF)->value('id')]);
    $this->admin = User::factory()->create(['role_id' => Role::where('name', Role::ADMIN)->value('id')]);

    $category = VehicleCategory::create(['name' => 'MPV']);
    $this->vehicle = Vehicle::create([
        'category_id' => $category->id,
        'name' => 'Toyota Avanza',
        'plate_number' => 'BP 1234 XX',
        'seat_capacity' => 7,
        'price_per_day' => 300000,
    ]);
});

function makeBooking(User $customer, Vehicle $vehicle, string $status): Booking
{
    return Booking::create([
        'booking_code' => 'WRC-TEST-'.uniqid(),
        'user_id' => $customer->id,
        'booking_type' => 'mobil',
        'vehicle_id' => $vehicle->id,
        'start_datetime' => now()->subDays(3),
        'end_datetime' => now()->subDay(),
        'duration_days' => 2,
        'with_driver' => false,
        'delivery_method' => 'pickup_at_office',
        'total_price' => 600000,
        'status' => $status,
    ]);
}

test('customer cannot review a booking that is not yet selesai', function () {
    $booking = makeBooking($this->customer, $this->vehicle, 'dikonfirmasi');

    $this->actingAs($this->customer)->get("/booking/{$booking->id}/ulasan/tulis")->assertForbidden();
    $this->actingAs($this->customer)->post("/booking/{$booking->id}/ulasan", ['rating' => 5])->assertForbidden();
});

test('customer cannot review someone else\'s booking', function () {
    $booking = makeBooking($this->otherCustomer, $this->vehicle, 'selesai');

    $this->actingAs($this->customer)->get("/booking/{$booking->id}/ulasan/tulis")->assertForbidden();
});

test('customer can review a finished booking exactly once', function () {
    $booking = makeBooking($this->customer, $this->vehicle, 'selesai');

    $this->actingAs($this->customer)
        ->get("/booking/{$booking->id}/ulasan/tulis")
        ->assertInertia(fn (Assert $page) => $page->component('Ulasan/Create'));

    $this->actingAs($this->customer)
        ->post("/booking/{$booking->id}/ulasan", ['rating' => 4, 'comment' => 'Mantap'])
        ->assertRedirect(route('booking.show', $booking));

    $this->assertDatabaseHas('reviews', [
        'booking_id' => $booking->id,
        'vehicle_id' => $this->vehicle->id,
        'rating' => 4,
    ]);

    // Second attempt is blocked because the booking already has a review.
    $this->actingAs($this->customer)->get("/booking/{$booking->id}/ulasan/tulis")->assertForbidden();
});

test('staff is blocked from moderating reviews', function () {
    $this->actingAs($this->staff)->get('/admin/ulasan')->assertForbidden();
});

test('admin can toggle review visibility and hidden reviews do not show publicly', function () {
    $booking = makeBooking($this->customer, $this->vehicle, 'selesai');
    $review = Review::create([
        'booking_id' => $booking->id,
        'user_id' => $this->customer->id,
        'vehicle_id' => $this->vehicle->id,
        'rating' => 5,
        'comment' => 'Bagus sekali',
    ]);

    $this->actingAs($this->admin)
        ->get("/mobil/{$this->vehicle->slug}")
        ->assertInertia(fn (Assert $page) => $page->where('reviews.0.comment', 'Bagus sekali'));

    $this->actingAs($this->admin)->patch("/admin/ulasan/{$review->id}/toggle")->assertRedirect();

    $review->refresh();
    expect($review->is_hidden)->toBeTrue();

    $this->get("/mobil/{$this->vehicle->slug}")
        ->assertInertia(fn (Assert $page) => $page->where('reviews', []));
});
