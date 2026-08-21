<?php

use App\Models\Role;
use App\Models\TourPackage;
use App\Models\User;
use App\Models\Vehicle;
use App\Models\VehicleCategory;
use App\Models\VehicleImage;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

beforeEach(function () {
    $this->seed(RolePermissionSeeder::class);
    $this->admin = User::factory()->create(['role_id' => Role::where('name', Role::ADMIN)->value('id')]);
});

/**
 * Regression guard: Route::resource('paket-wisata', ...) auto-singularizes
 * to {paket_wisatum} (Laravel's inflector treats "wisata" as a Latin plural
 * like "data"/"datum"), which didn't match the controller's $paket_wisata
 * parameter. Implicit route binding silently failed and the controller
 * received a fresh, unsaved TourPackage instance instead of the real
 * record - update()/delete() on a non-existent model are no-ops in Eloquent,
 * so the admin saw a "success" redirect while nothing actually changed.
 */
test('admin editing a tour package actually persists the changes', function () {
    $package = TourPackage::create(['name' => 'Paket Lama', 'seat_capacity' => 4, 'duration_days' => 1, 'price' => 500000]);

    $this->actingAs($this->admin)
        ->put("/admin/paket-wisata/{$package->id}", [
            'name' => 'Paket Baru',
            'seat_capacity' => 6,
            'duration_days' => 3,
            'price' => 900000,
        ])
        ->assertRedirect(route('admin.paket-wisata.index'));

    $this->assertDatabaseHas('tour_packages', [
        'id' => $package->id,
        'name' => 'Paket Baru',
        'seat_capacity' => 6,
        'duration_days' => 3,
    ]);
});

test('admin deleting a tour package actually removes it', function () {
    $package = TourPackage::create(['name' => 'Paket Dihapus', 'seat_capacity' => 4, 'duration_days' => 1, 'price' => 500000]);

    $this->actingAs($this->admin)
        ->delete("/admin/paket-wisata/{$package->id}")
        ->assertRedirect();

    $this->assertSoftDeleted('tour_packages', ['id' => $package->id]);
});

test('admin editing a vehicle category actually persists the changes', function () {
    $category = VehicleCategory::create(['name' => 'Kategori Lama']);

    $this->actingAs($this->admin)
        ->put("/admin/kategori-mobil/{$category->id}", ['name' => 'Kategori Baru'])
        ->assertRedirect(route('admin.kategori-mobil.index'));

    $this->assertDatabaseHas('vehicle_categories', ['id' => $category->id, 'name' => 'Kategori Baru']);
});

test('admin editing a vehicle actually persists the changes', function () {
    $category = VehicleCategory::create(['name' => 'MPV']);
    $vehicle = Vehicle::create([
        'category_id' => $category->id,
        'name' => 'Mobil Lama',
        'plate_number' => 'BP 9999 ZZ',
        'seat_capacity' => 5,
        'price_per_day' => 300000,
    ]);

    $this->actingAs($this->admin)
        ->put("/admin/mobil/{$vehicle->id}", [
            'category_id' => $category->id,
            'name' => 'Mobil Baru',
            'plate_number' => 'BP 9999 ZZ',
            'transmission' => 'automatic',
            'fuel_type' => 'bensin',
            'seat_capacity' => 7,
            'price_per_day' => 450000,
            'status' => 'tersedia',
        ])
        ->assertRedirect(route('admin.mobil.index'));

    $this->assertDatabaseHas('vehicles', ['id' => $vehicle->id, 'name' => 'Mobil Baru', 'price_per_day' => 450000]);
});

test('replacing a vehicle category icon deletes the old file from storage', function () {
    Storage::fake('public');

    $category = VehicleCategory::create(['name' => 'Kategori Foto']);
    $oldPath = UploadedFile::fake()->image('old-icon.jpg')->store('vehicle-categories', 'public');
    $category->update(['icon_path' => Storage::disk('public')->url($oldPath)]);

    Storage::disk('public')->assertExists($oldPath);

    $this->actingAs($this->admin)->put("/admin/kategori-mobil/{$category->id}", [
        'name' => 'Kategori Foto',
        'icon' => UploadedFile::fake()->image('new-icon.jpg'),
    ])->assertRedirect(route('admin.kategori-mobil.index'));

    Storage::disk('public')->assertMissing($oldPath);
});

test('deleting the primary vehicle image promotes another image to primary', function () {
    Storage::fake('public');

    $category = VehicleCategory::create(['name' => 'MPV']);
    $vehicle = Vehicle::create([
        'category_id' => $category->id,
        'name' => 'Mobil Foto',
        'plate_number' => 'BP 8888 ZZ',
        'seat_capacity' => 5,
        'price_per_day' => 300000,
    ]);

    $primary = VehicleImage::create([
        'vehicle_id' => $vehicle->id,
        'image_path' => Storage::disk('public')->url(UploadedFile::fake()->image('a.jpg')->store('vehicles', 'public')),
        'is_primary' => true,
        'sort_order' => 0,
    ]);
    $secondary = VehicleImage::create([
        'vehicle_id' => $vehicle->id,
        'image_path' => Storage::disk('public')->url(UploadedFile::fake()->image('b.jpg')->store('vehicles', 'public')),
        'is_primary' => false,
        'sort_order' => 1,
    ]);

    $this->actingAs($this->admin)
        ->delete("/admin/mobil/{$vehicle->id}/images/{$primary->id}")
        ->assertRedirect();

    $this->assertDatabaseMissing('vehicle_images', ['id' => $primary->id]);
    $this->assertDatabaseHas('vehicle_images', ['id' => $secondary->id, 'is_primary' => true]);
});
