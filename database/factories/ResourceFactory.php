<?php

namespace Database\Factories;

use App\Models\Category;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Resource>
 */
class ResourceFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $category = Category::inRandomOrder()->first() ?? Category::factory()->create();

        $specs = match ($category->name) {
            "VM" => [
                "cpu" => rand(2, 8) . " vCPU",
                "ram" => rand(4, 32) . "GB",
                "allow_os" => true,
            ],
            "Server" => [
                "cpu" => rand(16, 64) . " Cores",
                "ram" => rand(64, 512) . "GB",
                "rack" => "A" . rand(1, 9),
            ],
            "Storage" => [
                "capacity" => rand(10, 100) . "TB",
                "type" => "SSD/NVMe",
            ],
            "Network" => ["bandwidth" => "10Gbps", "ports" => 48],
            default => [],
        };

        return [
            "name" =>
                strtoupper($category->name) .
                "-" .
                fake()->unique()->numerify("####"),
            "category_id" => $category->id,
            "specs" => $specs,
            "status" => fake()->boolean(99) ? "Enabled" : "Disabled", // ~1% disabled
        ];
    }
}
