<?php

namespace Tests\Unit\Core\UseCases\Locations;

use App\Core\UseCases\Locations\HaveSlots;
use App\Models\Location;
use App\Models\Booking;
use App\Models\TimeSlot;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class HaveSlotsTest extends TestCase
{
    use RefreshDatabase;

    public function test_location_has_enough_capacity()
    {
        // Arrange
        $location = Location::factory()->create(['capacity' => 50]);

        $slotId = TimeSlot::factory()->create([
            'location_id' => $location->id,
            'day_of_week' => 1,
            'start_time' => '10:00:00',
            'end_time' => '11:00:00',
            'cost_per_hour' => 10,
        ])->id;

        $booking = Booking::factory()->create([
            'location_id' => $location->id,
            'date' => '2024-12-02',
        ]);

        $booking->invites()->create([
            'name' => 'test',
            'last_name' => 'test',
            'dni' => '123456789',
        ]);


        $booking->timeSlots()->attach([$slotId]);
        $peopleCount = 1;

        $useCase = new HaveSlots();
        // Act
        $result = $useCase->execute($location->id, $slotId, '2024-12-02', $peopleCount);

        // Assert
        $this->assertTrue($result);
    }

    public function test_location_does_not_have_enough_capacity()
    {
        // Arrange
        $location = Location::factory()->create([
            'capacity' => 1, // La capacidad de la locación es 1
        ]);
    
        $slot = $location->timeSlots()->create([
            'day_of_week' => 1,
            'start_time' => '10:00:00',
            'end_time' => '11:00:00',
            'cost_per_hour' => 10,
        ]);
    
        $booking = Booking::factory()->create([
            'location_id' => $location->id,
            'date' => '2024-12-02',
        ]);
    
        // Crea un invitado para consumir la capacidad
        $booking->invites()->create([
            'name' => 'Test',
            'last_name' => 'User',
            'dni' => '123456789',
        ]);
    
        // Asocia el booking al slot creado
        $booking->timeSlots()->attach([$slot->id]);
    
        // La cantidad de personas excede la capacidad
        $peopleCount = 2;
    
        // Mock del caso de uso para verificar disponibilidad
        $useCase = app(HaveSlots::class); // Asegúrate de que HaveSlots esté configurado en el contenedor de servicios
    
        // Act
        $result = $useCase->execute(
            $location->id,
            $slot->id,
            '2024-12-02',
            $peopleCount
        );
    
        // Assert
        $this->assertFalse($result); // Debe retornar falso porque no hay suficiente capacidad
    }
    

    public function test_location_not_found_throws_exception()
    {
        $this->expectException(\Illuminate\Database\Eloquent\ModelNotFoundException::class);

        $useCase = new HaveSlots();
        $useCase->execute(999, 1, '2024-12-02', 5); // Non-existent location ID
    }

    public function test_no_bookings_for_date_and_slot_returns_true()
    {
        // Arrange
        $location = Location::factory()->create(['capacity' => 30]);
        $slotId = 1;
        $peopleCount = 10;

        $useCase = new HaveSlots();

        // Act
        $result = $useCase->execute($location->id, $slotId, '2024-12-02', $peopleCount);

        // Assert
        $this->assertTrue($result);
    }
}
