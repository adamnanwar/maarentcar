<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Car;
use App\Models\TourPackage;
use App\Models\TourItinerary;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Create admin user
        User::create([
            'name' => 'Admin MaaRentCar',
            'email' => 'admin@maarentcar.com',
            'phone' => '081234567890',
            'password' => Hash::make('password'),
            'role' => 'ADMIN',
            'email_verified_at' => now(),
        ]);

        // Create sample customer
        User::create([
            'name' => 'Customer Demo',
            'email' => 'customer@example.com',
            'phone' => '081234567891',
            'password' => Hash::make('password'),
            'role' => 'CUSTOMER',
            'email_verified_at' => now(),
        ]);

        // Create sample cars
        $cars = [
            [
                'name' => 'Toyota Avanza',
                'brand' => 'Toyota',
                'type' => 'MPV',
                'transmission' => 'Automatic',
                'seats' => 7,
                'price_per_day' => 350000,
                'with_driver_price_per_day' => 500000,
                'is_active' => true,
            ],
            [
                'name' => 'Honda Brio',
                'brand' => 'Honda',
                'type' => 'Hatchback',
                'transmission' => 'Automatic',
                'seats' => 5,
                'price_per_day' => 300000,
                'with_driver_price_per_day' => 450000,
                'is_active' => true,
            ],
            [
                'name' => 'Toyota Innova Reborn',
                'brand' => 'Toyota',
                'type' => 'MPV',
                'transmission' => 'Automatic',
                'seats' => 7,
                'price_per_day' => 550000,
                'with_driver_price_per_day' => 700000,
                'is_active' => true,
            ],
            [
                'name' => 'Mitsubishi Pajero Sport',
                'brand' => 'Mitsubishi',
                'type' => 'SUV',
                'transmission' => 'Automatic',
                'seats' => 7,
                'price_per_day' => 800000,
                'with_driver_price_per_day' => 1000000,
                'is_active' => true,
            ],
            [
                'name' => 'Toyota Fortuner',
                'brand' => 'Toyota',
                'type' => 'SUV',
                'transmission' => 'Automatic',
                'seats' => 7,
                'price_per_day' => 900000,
                'with_driver_price_per_day' => 1100000,
                'is_active' => true,
            ],
            [
                'name' => 'Honda Jazz',
                'brand' => 'Honda',
                'type' => 'Hatchback',
                'transmission' => 'Manual',
                'seats' => 5,
                'price_per_day' => 350000,
                'with_driver_price_per_day' => 500000,
                'is_active' => true,
            ],
            [
                'name' => 'Toyota Camry',
                'brand' => 'Toyota',
                'type' => 'Sedan',
                'transmission' => 'Automatic',
                'seats' => 5,
                'price_per_day' => 700000,
                'with_driver_price_per_day' => 900000,
                'is_active' => true,
            ],
            [
                'name' => 'Toyota Alphard',
                'brand' => 'Toyota',
                'type' => 'Luxury',
                'transmission' => 'Automatic',
                'seats' => 7,
                'price_per_day' => 1500000,
                'with_driver_price_per_day' => 1800000,
                'is_active' => true,
            ],
            [
                'name' => 'Suzuki Ertiga',
                'brand' => 'Suzuki',
                'type' => 'MPV',
                'transmission' => 'Automatic',
                'seats' => 7,
                'price_per_day' => 350000,
                'with_driver_price_per_day' => 500000,
                'is_active' => true,
            ],
            [
                'name' => 'Hyundai Stargazer',
                'brand' => 'Hyundai',
                'type' => 'MPV',
                'transmission' => 'Automatic',
                'seats' => 7,
                'price_per_day' => 400000,
                'with_driver_price_per_day' => 550000,
                'is_active' => true,
            ],
        ];

        foreach ($cars as $car) {
            Car::create($car);
        }

        // Create sample tour packages
        $packages = [
            [
                'name' => 'Paket Wisata Bromo Sunrise',
                'description' => 'Nikmati pemandangan matahari terbit di Gunung Bromo dengan paket wisata lengkap. Termasuk guide lokal dan dokumentasi.',
                'price' => 750000,
                'duration_days' => 2,
                'is_active' => true,
                'itineraries' => [
                    ['day_number' => 1, 'activity' => 'Penjemputan dari hotel, perjalanan menuju area Bromo, check-in penginapan, persiapan untuk sunrise trip.', 'time' => '08:00'],
                    ['day_number' => 2, 'activity' => 'Berangkat menuju view point sunrise, menikmati sunrise, eksplorasi kawah Bromo, kembali ke hotel/kota asal.', 'time' => '03:00'],
                ]
            ],
            [
                'name' => 'Paket Wisata Malang City Tour',
                'description' => 'Jelajahi keindahan kota Malang dan sekitarnya dalam satu hari penuh. Mengunjungi tempat-tempat ikonik dan kuliner khas.',
                'price' => 450000,
                'duration_days' => 1,
                'is_active' => true,
                'itineraries' => [
                    ['day_number' => 1, 'activity' => 'Kunjungan ke Jatim Park, Museum Angkut, Kampung Warna-Warni, Alun-alun Malang, dan wisata kuliner bakso Malang.', 'time' => '08:00'],
                ]
            ],
            [
                'name' => 'Paket Wisata Batu 3H2M',
                'description' => 'Paket lengkap wisata Batu selama 3 hari 2 malam. Mengunjungi berbagai wahana dan tempat wisata populer.',
                'price' => 1500000,
                'duration_days' => 3,
                'is_active' => true,
                'itineraries' => [
                    ['day_number' => 1, 'activity' => 'Penjemputan, check-in hotel, mengunjungi Jatim Park 1 dan Batu Night Spectacular.', 'time' => '10:00'],
                    ['day_number' => 2, 'activity' => 'Mengunjungi Museum Angkut, Taman Selecta, dan Coban Rondo Waterfall.', 'time' => '08:00'],
                    ['day_number' => 3, 'activity' => 'Mengunjungi area Paralayang Batu, oleh-oleh khas Batu, kembali ke kota asal.', 'time' => '09:00'],
                ]
            ],
            [
                'name' => 'Paket Pantai Selatan',
                'description' => 'Nikmati keindahan pantai-pantai selatan Malang yang eksotis. Cocok untuk pecinta pantai dan sunset.',
                'price' => 600000,
                'duration_days' => 1,
                'is_active' => true,
                'itineraries' => [
                    ['day_number' => 1, 'activity' => 'Kunjungan ke Pantai Balekambang, Pantai Goa Cina, Pantai Sendang Biru, dan menikmati seafood segar.', 'time' => '07:00'],
                ]
            ],
        ];

        foreach ($packages as $packageData) {
            $itineraries = $packageData['itineraries'];
            unset($packageData['itineraries']);

            $package = TourPackage::create($packageData);

            foreach ($itineraries as $itinerary) {
                TourItinerary::create([
                    'tour_package_id' => $package->id,
                    ...$itinerary,
                ]);
            }
        }
    }
}
