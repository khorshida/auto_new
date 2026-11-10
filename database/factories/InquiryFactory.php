<?php

namespace Database\Factories;

use App\Enums\InquiryStatus;
use App\Enums\InquiryType;
use App\Models\Car;
use App\Models\Inquiry;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Inquiry>
 */
class InquiryFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => fake()->name(),
            'phone' => fake()->numerify('01#########'),
            'message' => fake()->sentence(),
            'type' => InquiryType::Interest,
            'car_id' => Car::factory(),
            'status' => InquiryStatus::New,
        ];
    }

    public function sell(): static
    {
        return $this->state(fn (array $attributes) => [
            'type' => InquiryType::Sell,
            'car_id' => null,
            'message' => 'I want to sell my car',
        ]);
    }
}
