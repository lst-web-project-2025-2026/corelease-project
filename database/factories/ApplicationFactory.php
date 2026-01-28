<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Application>
 */
class ApplicationFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $status = fake()->randomElement(["Pending", "Approved", "Rejected"]);

        return [
            "name" => fake()->name(),
            "email" => fake()->unique()->safeEmail(),
            "password" => Hash::make("password"),
            "profession" => fake()->jobTitle(),
            "user_justification" =>
                "I require access for " . fake()->sentence(),
            "status" => $status,
            "decided_by" => null, // Will be filled in seeder
            // Business Logic:
            "admin_justification" => match ($status) {
                "Pending" => null,
                "Approved" => "Approved: " . fake()->sentence(),
                "Rejected"
                    => "Rejected: Account request does not meet security criteria.",
            },
        ];
    }
}
