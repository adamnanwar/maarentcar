<?php

namespace Database\Seeders;

use App\Models\Destination;
use App\Models\TourPackage;
use App\Models\Vehicle;
use App\Models\VehicleCategory;
use App\Models\VehicleImage;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Storage;

class CatalogSeeder extends Seeder
{
    public function run(): void
    {
        $categories = [
            'City Car' => 'Mobil kompak, irit, mudah bermanuver di jalanan kota.',
            'MPV' => 'Mobil keluarga dengan kapasitas kursi lega.',
            'SUV' => 'Mobil tangguh untuk medan campuran dan perjalanan jauh.',
            'Minibus' => 'Kapasitas besar untuk rombongan dan wisata grup.',
        ];

        $categoryModels = [];
        foreach ($categories as $name => $description) {
            $categoryModels[$name] = VehicleCategory::updateOrCreate(
                ['name' => $name],
                ['description' => $description]
            );
        }

        $vehicles = [
            ['category' => 'City Car', 'name' => 'Honda Brio', 'brand' => 'Honda', 'model' => 'Brio', 'year' => 2023, 'plate' => 'BP 1001 XX', 'transmission' => 'automatic', 'seats' => 5, 'price' => 350000, 'driver_fee' => 150000],
            ['category' => 'City Car', 'name' => 'Toyota Agya', 'brand' => 'Toyota', 'model' => 'Agya', 'year' => 2022, 'plate' => 'BP 1002 XX', 'transmission' => 'manual', 'seats' => 5, 'price' => 300000, 'driver_fee' => 150000],
            ['category' => 'MPV', 'name' => 'Toyota Avanza', 'brand' => 'Toyota', 'model' => 'Avanza', 'year' => 2023, 'plate' => 'BP 1003 XX', 'transmission' => 'manual', 'seats' => 7, 'price' => 400000, 'driver_fee' => 150000],
            ['category' => 'MPV', 'name' => 'Mitsubishi Xpander', 'brand' => 'Mitsubishi', 'model' => 'Xpander', 'year' => 2023, 'plate' => 'BP 1004 XX', 'transmission' => 'automatic', 'seats' => 7, 'price' => 450000, 'driver_fee' => 175000],
            ['category' => 'SUV', 'name' => 'Toyota Fortuner', 'brand' => 'Toyota', 'model' => 'Fortuner', 'year' => 2023, 'plate' => 'BP 1005 XX', 'transmission' => 'automatic', 'seats' => 7, 'price' => 900000, 'driver_fee' => 250000],
            ['category' => 'Minibus', 'name' => 'Toyota Hiace', 'brand' => 'Toyota', 'model' => 'Hiace', 'year' => 2022, 'plate' => 'BP 1006 XX', 'transmission' => 'manual', 'seats' => 15, 'price' => 1200000, 'driver_fee' => 300000],
        ];

        $vehicleModels = [];
        foreach ($vehicles as $v) {
            $vehicleModels[$v['name']] = Vehicle::updateOrCreate(
                ['plate_number' => $v['plate']],
                [
                    'category_id' => $categoryModels[$v['category']]->id,
                    'name' => $v['name'],
                    'brand' => $v['brand'],
                    'model' => $v['model'],
                    'year' => $v['year'],
                    'transmission' => $v['transmission'],
                    'fuel_type' => 'bensin',
                    'seat_capacity' => $v['seats'],
                    'price_per_day' => $v['price'],
                    'driver_fee_per_day' => $v['driver_fee'],
                    'base_delivery_fee' => 50000,
                    'description' => "{$v['name']} tahun {$v['year']}, kondisi terawat, siap untuk perjalanan Anda di Batam.",
                    'status' => Vehicle::STATUS_TERSEDIA,
                    'is_active' => true,
                ]
            );
        }

        $vehicleImages = [
            'Honda Brio' => 'brio.jpeg',
            'Toyota Agya' => 'agya.jpeg',
            'Toyota Avanza' => 'avanza.jpeg',
            'Toyota Fortuner' => 'fortuner.jpg',
            'Toyota Hiace' => 'hiace.jpeg',
        ];

        $vehicleImageUrls = [];
        foreach ($vehicleImages as $vehicleName => $filename) {
            $vehicle = $vehicleModels[$vehicleName] ?? null;
            $sourcePath = public_path("imagebahan/{$filename}");

            if (! $vehicle || ! file_exists($sourcePath)) {
                continue;
            }

            $storedPath = "vehicles/{$filename}";

            if (! Storage::disk('public')->exists($storedPath)) {
                Storage::disk('public')->put($storedPath, file_get_contents($sourcePath));
            }

            $url = Storage::disk('public')->url($storedPath);
            $vehicleImageUrls[$vehicleName] = $url;

            if (! $vehicle->images()->exists()) {
                VehicleImage::create([
                    'vehicle_id' => $vehicle->id,
                    'image_path' => $url,
                    'is_primary' => true,
                    'sort_order' => 0,
                ]);
            }
        }

        $destinations = [
            ['name' => 'Pantai Sekilak', 'category' => 'pantai', 'addon' => 75000, 'desc' => 'Pantai pasir putih dengan air laut jernih, favorit wisatawan Batam.'],
            ['name' => 'Piayu Laut Seafood', 'category' => 'kuliner', 'addon' => 50000, 'desc' => 'Restoran seafood segar dengan pemandangan laut.'],
            ['name' => 'Jembatan Barelang', 'category' => 'sejarah', 'addon' => 60000, 'desc' => 'Ikon jembatan penghubung pulau-pulau di Batam.'],
            ['name' => 'Ocarina Batam', 'category' => 'hiburan', 'addon' => 80000, 'desc' => 'Kawasan wisata kuliner dan hiburan malam tepi laut.'],
            ['name' => 'Vihara Duta Maitreya', 'category' => 'sejarah', 'addon' => 40000, 'desc' => 'Vihara terbesar di Batam dengan arsitektur megah.'],
            ['name' => 'Nagoya Hill', 'category' => 'kuliner', 'addon' => 45000, 'desc' => 'Pusat perbelanjaan dan kuliner di jantung kota Batam.'],
        ];

        $destinationModels = [];
        foreach ($destinations as $d) {
            $destinationModels[$d['name']] = Destination::updateOrCreate(
                ['name' => $d['name']],
                [
                    'category' => $d['category'],
                    'description' => $d['desc'],
                    'addon_price' => $d['addon'],
                    'is_active' => true,
                ]
            );
        }

        $packages = [
            [
                'name' => 'Batam Highlight 1 Hari',
                'vehicle' => 'Toyota Avanza',
                'seats' => 7,
                'duration' => 1,
                'price' => 850000,
                'desc' => 'Jelajahi ikon utama Batam dalam satu hari penuh bersama supir berpengalaman.',
                'destinations' => ['Jembatan Barelang', 'Vihara Duta Maitreya', 'Nagoya Hill'],
            ],
            [
                'name' => 'Batam Kuliner & Pantai 1 Hari',
                'vehicle' => 'Mitsubishi Xpander',
                'seats' => 7,
                'duration' => 1,
                'price' => 950000,
                'desc' => 'Nikmati pantai indah dan seafood segar khas Batam.',
                'destinations' => ['Pantai Sekilak', 'Piayu Laut Seafood', 'Ocarina Batam'],
            ],
            [
                'name' => 'Batam Explorer 2 Hari 1 Malam',
                'vehicle' => 'Toyota Hiace',
                'seats' => 15,
                'duration' => 2,
                'price' => 2200000,
                'desc' => 'Paket lengkap 2 hari untuk rombongan, mencakup seluruh destinasi favorit Batam.',
                'destinations' => ['Pantai Sekilak', 'Jembatan Barelang', 'Ocarina Batam', 'Nagoya Hill'],
            ],
        ];

        foreach ($packages as $p) {
            $attributes = [
                'vehicle_id' => $vehicleModels[$p['vehicle']]->id,
                'seat_capacity' => $p['seats'],
                'duration_days' => $p['duration'],
                'driver_included' => true,
                'price' => $p['price'],
                'description' => $p['desc'],
                'is_active' => true,
            ];

            if (isset($vehicleImageUrls[$p['vehicle']])) {
                $attributes['image_path'] = $vehicleImageUrls[$p['vehicle']];
            }

            $package = TourPackage::updateOrCreate(['name' => $p['name']], $attributes);

            $sync = [];
            foreach (array_values($p['destinations']) as $order => $destinationName) {
                $sync[$destinationModels[$destinationName]->id] = ['sort_order' => $order];
            }
            $package->destinations()->sync($sync);
        }
    }
}
