<?php

namespace Database\Factories;

use App\Enums\CarImageKind;
use App\Models\Car;
use App\Models\CarImage;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<CarImage>
 */
class CarImageFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'car_id' => Car::factory(),
            'path' => 'demo/'.fake()->uuid().'.jpg',
            'kind' => CarImageKind::Gallery,
            'sort_order' => 0,
        ];
    }

    public function spin(): static
    {
        return $this->state(fn (array $attributes) => [
            'kind' => CarImageKind::Spin,
        ]);
    }
}
