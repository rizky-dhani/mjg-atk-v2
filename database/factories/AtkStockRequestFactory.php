<?php

namespace Database\Factories;

use App\Enums\AtkStockRequestStatus;
use App\Models\User;
use App\Models\UserDivision;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\AtkStockRequest>
 */
class AtkStockRequestFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'request_number' => 'REQ-'.fake()->unique()->numerify('#####'),
            'request_type' => 'Office Stationery',
            'status' => AtkStockRequestStatus::Draft,
            'requester_id' => User::factory(),
            'division_id' => UserDivision::factory(),
            'notes' => fake()->sentence(),
        ];
    }

    public function published(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => AtkStockRequestStatus::Published,
        ]);
    }
}
