<?php

namespace Tests\Feature;

use App\Models\Resource;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ReservationTest extends TestCase
{
    use RefreshDatabase;

    public function test_reservation_requires_valid_dates()
    {
        $user = User::factory()->create();
        $resource = Resource::factory()->create();

        $response = $this->actingAs($user)->post(route('reservations.store'), [
            'resource_id' => $resource->id,
            'start_date' => 'not-a-date',
            'end_date' => '2026-01-01',
            'user_justification' => 'Test justification',
        ]);

        $response->assertSessionHasErrors(['start_date']);
    }

    public function test_reservation_start_date_cannot_be_in_past()
    {
        $user = User::factory()->create();
        $resource = Resource::factory()->create();

        $response = $this->actingAs($user)->post(route('reservations.store'), [
            'resource_id' => $resource->id,
            'start_date' => now()->subDay()->format('Y-m-d'),
            'end_date' => now()->addDay()->format('Y-m-d'),
            'user_justification' => 'Test justification',
        ]);

        $response->assertSessionHasErrors(['start_date']);
    }

    public function test_reservation_end_date_must_be_after_start_date()
    {
        $user = User::factory()->create();
        $resource = Resource::factory()->create();

        $response = $this->actingAs($user)->post(route('reservations.store'), [
            'resource_id' => $resource->id,
            'start_date' => '2026-12-01',
            'end_date' => '2026-11-01',
            'user_justification' => 'Test justification',
        ]);

        $response->assertSessionHasErrors(['end_date']);
    }
}
