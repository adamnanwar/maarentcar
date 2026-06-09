<?php

namespace Database\Factories;

use App\Models\Car;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Car>
 */
class CarFactory extends Factory
{
    protected $model = Car::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $cars = [
            ['name' => 'Toyota Avanza', 'brand' => 'Toyota', 'type' => 'MPV', 'seats' => 7, 'price' => 350000, 'driver_price' => 500000],
            ['name' => 'Honda Brio', 'brand' => 'Honda', 'type' => 'Hatchback', 'seats' => 5, 'price' => 300000, 'driver_price' => 450000],
            ['name' => 'Toyota Innova Reborn', 'brand' => 'Toyota', 'type' => 'MPV', 'seats' => 7, 'price' => 550000, 'driver_price' => 700000],
            ['name' => 'Mitsubishi Pajero Sport', 'brand' => 'Mitsubishi', 'type' => 'SUV', 'seats' => 7, 'price' => 800000, 'driver_price' => 1000000],
            ['name' => 'Toyota Fortuner', 'brand' => 'Toyota', 'type' => 'SUV', 'seats' => 7, 'price' => 900000, 'driver_price' => 1100000],
            ['name' => 'Honda Jazz', 'brand' => 'Honda', 'type' => 'Hatchback', 'seats' => 5, 'price' => 350000, 'driver_price' => 500000],
            ['name' => 'Toyota Camry', 'brand' => 'Toyota', 'type' => 'Sedan', 'seats' => 5, 'price' => 700000, 'driver_price' => 900000],
            ['name' => 'Honda Civic', 'brand' => 'Honda', 'type' => 'Sedan', 'seats' => 5, 'price' => 600000, 'driver_price' => 800000],
            ['name' => 'Daihatsu Xenia', 'brand' => 'Daihatsu', 'type' => 'MPV', 'seats' => 7, 'price' => 300000, 'driver_price' => 450000],
            ['name' => 'Suzuki Ertiga', 'brand' => 'Suzuki', 'type' => 'MPV', 'seats' => 7, 'price' => 350000, 'driver_price' => 500000],
            ['name' => 'Toyota Alphard', 'brand' => 'Toyota', 'type' => 'Luxury', 'seats' => 7, 'price' => 1500000, 'driver_price' => 1800000],
            ['name' => 'Hyundai Stargazer', 'brand' => 'Hyundai', 'type' => 'MPV', 'seats' => 7, 'price' => 400000, 'driver_price' => 550000],
        ];

        $car = $this->faker->randomElement($cars);

        return [
            'name' => $car['name'],
            'brand' => $car['brand'],
            'type' => $car['type'],
            'transmission' => $this->faker->randomElement(['Automatic', 'Manual']),
            'seats' => $car['seats'],
            'price_per_day' => $car['price'],
            'with_driver_price_per_day' => $car['driver_price'],
            'is_active' => true,
            'thumbnail_url' => null,
        ];
    }

    /**
     * Indicate that the car is inactive.
     */
    public function inactive(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_active' => false,
        ]);
    }
}
