<?php

namespace Tests\Feature;

use App\Models\Booking;
use App\Models\Location;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BookingApiControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_can_list_bookings()
    {
        $user = User::factory()->create();
        $this->actingAs($user, 'sanctum');

        Booking::factory()->count(3)->create(['user_id' => $user->id]);

        $response = $this->getJson('/api/bookings');

        $response->assertStatus(200)
            ->assertJsonCount(3);
    }

    public function test_it_can_show_a_booking()
    {
        $user = User::factory()->create();
        $this->actingAs($user, 'sanctum');

        $booking = Booking::factory()->create(['user_id' => $user->id]);

        $response = $this->getJson("/api/bookings/" . $booking->id);

        $response->assertStatus(200)
            ->assertJsonFragment([
                'id' => $booking->id,
                'location_id' => $booking->location_id,
                'people_count' => $booking->people_count,
            ]);
    }

    public function test_it_can_delete_a_booking()
    {
        $user = User::factory()->create();
        $this->actingAs($user, 'sanctum');

        $booking = Booking::factory()->create(['user_id' => $user->id]);

        $response = $this->deleteJson("/api/bookings/{$booking->id}");

        $response->assertStatus(204);

        $this->assertDatabaseMissing('bookings', ['id' => $booking->id]);
    }
}
