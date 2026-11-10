<?php

namespace Database\Factories;

use App\Enums\BodyStyle;
use App\Enums\CarCondition;
use App\Enums\CarStatus;
use App\Enums\DriveType;
use App\Enums\FuelType;
use App\Enums\Transmission;
use App\Models\Brand;
use App\Models\Car;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Car>
 */
class CarFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $name = fake()->words(2, true);

        return [
            'brand_id' => Brand::factory(),
            'name' => $name,
            'slug' => Str::slug($name).'-'.fake()->unique()->numerify('####'),
            'year' => fake()->numberBetween(2016, 2026),
            'mileage' => fake()->numberBetween(0, 80000),
            'engine' => '2.0L',
            'fuel' => fake()->randomElement(FuelType::cases()),
            'transmission' => Transmission::Automatic,
            'drive_type' => fake()->randomElement(DriveType::cases()),
            'body_style' => fake()->randomElement(BodyStyle::cases()),
            'exterior_colour' => fake()->safeColorName(),
            'condition' => fake()->randomElement(CarCondition::cases()),
            'status' => CarStatus::Available,
            'price' => fake()->numberBetween(1500000, 15000000),
            'contact_for_price' => false,
            'description' => fake()->sentence(),
            'featured' => false,
        ];
    }

    public function featured(): static
    {
        return $this->state(fn (array $attributes) => [
            'featured' => true,
        ]);
    }

    public function reserved(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => CarStatus::Reserved,
        ]);
    }

    public function sold(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => CarStatus::Sold,
        ]);
    }

    public function contactForPrice(): static
    {
        return $this->state(fn (array $attributes) => [
            'price' => null,
            'contact_for_price' => true,
        ]);
    }
}
