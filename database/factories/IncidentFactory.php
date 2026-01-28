<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Incident>
 */
class IncidentFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            "description" =>
                "Error code " . rand(100, 999) . ": " . fake()->sentence(),
            "status" => fake()->randomElement(["Open", "Resolved"]),
        ];
    }
}
