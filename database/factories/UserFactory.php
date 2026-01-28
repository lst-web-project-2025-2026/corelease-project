<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\User>
 */
class UserFactory extends Factory
{
    /**
     * The current password being used by the factory.
     */
    protected static ?string $password;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            "name" => fake()->name(),
            "email" => fake()->unique()->safeEmail(),
            "password" => (static::$password ??= Hash::make("password")),
            "profession" => fake()->jobTitle(),
            "role" => "User",
            "is_active" => fake()->boolean(99), // 1% chance of being disabled
            "remember_token" => Str::random(10),
        ];
    }

    public function admin(): static
    {
        return $this->state(
            fn(array $attributes) => [
                "role" => "Admin",
                "is_active" => true, // Admins should always be active
            ],
        );
    }

    public function manager(): static
    {
        return $this->state(
            fn(array $attributes) => [
                "role" => "Manager",
                "is_active" => true, // Managers should always be active
            ],
        );
    }

    /**
     * Indicate that the model's email address should be unverified.
     */
    public function unverified(): static
    {
        return $this->state(
            fn(array $attributes) => [
                "email_verified_at" => null,
            ],
        );
    }
}
