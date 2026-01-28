<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Maintenance>
 */
class MaintenanceFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        // Generate logical dates
        $start = fake()->dateTimeBetween("-2 weeks", "+2 weeks");
        $end = (clone $start)->modify("+" . rand(1, 3) . " days");

        return [
            "start_date" => $start->format("Y-m-d"),
            "end_date" => $end->format("Y-m-d"),
            "description" => "Routine maintenance: " . fake()->bs(),
        ];
    }
}
