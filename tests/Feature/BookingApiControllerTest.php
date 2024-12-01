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

    public function test_it_can_create_booking_within_time_slot()
    {
        $user = User::factory()->create();
        $this->actingAs($user, 'sanctum');

        $location = Location::factory()->create(['capacity' => 20]);

        // Crear un TimeSlot válido
        $location->timeSlots()->create([
            'day_of_week' => 3,
            'start_time' => '10:00',
            'end_time' => '12:00',
            'cost_per_hour' => 1000,
        ]);

        $data = [
            'location_id' => $location->id,
            'start_time' => '2024-11-20 10:30:00',
            'end_time' => '2024-11-20 11:30:00',
            'people_count' => 10,
        ];

        $response = $this->postJson('/api/bookings', $data);

        $response->assertStatus(201)
            ->assertJsonFragment([
                'location_id' => $location->id,
                'start_time' => '2024-11-20 10:30:00',
                'end_time' => '2024-11-20 11:30:00',
                'people_count' => 10,
            ]);

        $this->assertDatabaseHas('bookings', array_merge($data, ['user_id' => $user->id]));
    }

    public function test_it_fails_to_create_booking_outside_time_slots()
    {
        $user = User::factory()->create();
        $this->actingAs($user, 'sanctum');

        $location = Location::factory()->create(['capacity' => 20]);

        $location->timeSlots()->create([
            'day_of_week' => 3,
            'start_time' => '10:00',
            'end_time' => '12:00',
            'cost_per_hour' => 1000,
        ]);

        $data = [
            'location_id' => $location->id,
            'start_time' => '2024-11-20 08:00:00',
            'end_time' => '2024-11-20 09:30:00',
            'people_count' => 10,
        ];

        $response = $this->postJson('/api/bookings', $data);

        $response->assertStatus(422)
            ->assertJson(['message' => 'El horario solicitado no está disponible.']);
    }

    public function test_it_fails_to_create_booking_if_capacity_exceeds()
    {
        $user = User::factory()->create();
        $this->actingAs($user, 'sanctum');

        $location = Location::factory()->create(['capacity' => 20]);

        $location->timeSlots()->create([
            'day_of_week' => 3,
            'start_time' => '10:00',
            'end_time' => '12:00',
            'cost_per_hour' => 1000,
        ]);

        Booking::factory()->create([
            'location_id' => $location->id,
            'user_id' => $user->id,
            'start_time' => '2024-11-20 10:00:00',
            'end_time' => '2024-11-20 11:00:00',
            'people_count' => 15,
        ]);

        $data = [
            'location_id' => $location->id,
            'start_time' => '2024-11-20 10:30:00',
            'end_time' => '2024-11-20 11:30:00',
            'people_count' => 10,
        ];

        $response = $this->postJson('/api/bookings', $data);

        $response->assertStatus(422)
            ->assertJson(['message' => 'Capacidad excedida para este horario.']);
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
