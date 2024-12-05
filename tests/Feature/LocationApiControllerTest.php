<?php

namespace Tests\Feature;

use App\Models\Location;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LocationApiControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_can_list_locations()
    {
        Location::factory()->count(3)->create();

        $response = $this->getJson('/api/locations');

        $response->assertStatus(200)
            ->assertJsonStructure([
                '*' => ['id', 'name', 'capacity', 'created_at', 'updated_at'],
            ])
            ->assertJsonCount(3);
    }

    public function test_it_can_show_a_location()
    {
        $location = Location::factory()->create();

        $response = $this->getJson("/api/locations/{$location->id}");

        $response->assertStatus(200)
            ->assertJson([
                'id' => $location->id,
                'name' => $location->name,
                'capacity' => $location->capacity,
            ]);
    }

    public function test_it_checks_availability_based_on_time_slots()
    {
        // Arrange: Crear una locación con capacidad
        $location = Location::factory()->create(['capacity' => 20]);
    
        // Crear dos TimeSlots asociados a la locación
        $timeSlots = $location->timeSlots()->createMany([
            [
                'day_of_week' => 1, // Lunes
                'start_time' => '08:00',
                'end_time' => '12:00',
                'cost_per_hour' => 1000,
            ],
            [
                'day_of_week' => 1, // Lunes
                'start_time' => '14:00',
                'end_time' => '18:00',
                'cost_per_hour' => 1000,
            ],
        ]);
    
        // Extraer los IDs de los TimeSlots creados
        $timeSlotIds = $timeSlots->pluck('id');
    
        // Act: Consultar la API para verificar disponibilidad
        $response = $this->getJson("/api/locations/{$location->id}/availability?date=2024-11-18");
    
        // Assert: Verificar la respuesta
        $response->assertStatus(200)
            ->assertJsonFragment([
                'timeSlot_id' => $timeSlotIds[0],
                'startTime' => '08:00',
            ])
            ->assertJsonFragment([
                'timeSlot_id' => $timeSlotIds[1],
                'startTime' => '14:00',
            ]);
    }
    

    public function test_it_fails_to_check_availability_without_date_parameter()
    {
        $location = Location::factory()->create(['capacity' => 20]);

        $response = $this->getJson("/api/locations/{$location->id}/availability");

        $response->assertStatus(422)
            ->assertJson([
                'message' => 'El parámetro "date" es obligatorio.',
            ]);
    }
}
