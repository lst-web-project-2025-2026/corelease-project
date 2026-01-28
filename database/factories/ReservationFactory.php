<?php

namespace Database\Factories;

use App\Models\Resource;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Reservation>
 */
class ReservationFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $status = fake()->randomElement([
            "Pending",
            "Approved",
            "Rejected",
            "Completed",
        ]);

        $decidedBy = null;
        if ($status !== "Pending") {
            // This will be overridden in the seeder for better logic,
            // but for standalone factory use we can just put a placeholder or null
            $decidedBy = null;
        }

        $start = fake()->dateTimeBetween("-1 month", "+1 month");
        $end = (clone $start)->modify("+" . rand(1, 14) . " days");

        return [
            // Default associations if none are provided
            "user_id" => User::factory(),
            "resource_id" => Resource::factory(),

            "start_date" => $start->format("Y-m-d"),
            "end_date" => $end->format("Y-m-d"),
            "user_justification" =>
                "Project " .
                strtoupper(fake()->word()) .
                ": " .
                fake()->paragraph(2),
            "status" => $status,
            "decided_by" => $decidedBy,

            "manager_justification" => match ($status) {
                "Pending" => null,
                "Approved",
                "Completed"
                    => "Technical requirements verified. Reservation authorized.",
                "Rejected" => "Denied: " .
                    fake()->sentence() .
                    " (Conflict with " .
                    fake()->word() .
                    " window).",
            },

            // DYNAMIC CONFIGURATION LOGIC
            "configuration" => function (array $attributes) {
                // Find the resource being assigned to this reservation
                $resource = Resource::find($attributes["resource_id"]);

                // Base configuration (applies to all)
                $config = [
                    "backup_enabled" => fake()->boolean(70),
                    "monitoring_level" => fake()->randomElement([
                        "Standard",
                        "Advanced",
                    ]),
                ];

                // Logic: Only add OS if the resource specs allow it
                if (
                    $resource &&
                    isset($resource->specs["allow_os"]) &&
                    $resource->specs["allow_os"] === true
                ) {
                    $config["os"] = fake()->randomElement([
                        "Ubuntu 22.04 LTS",
                        "Debian 12",
                        "CentOS Stream 9",
                        "Windows Server 2022",
                    ]);
                }

                return $config;
            },
        ];
    }
}
