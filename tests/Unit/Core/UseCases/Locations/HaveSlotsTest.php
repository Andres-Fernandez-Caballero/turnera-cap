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
            'people_count' => 20,
        ]);


        $booking->timeSlots()->attach([$slotId]);
        $peopleCount = 20;

        $useCase = new HaveSlots();

        // Act
        $result = $useCase->execute($location->id, $slotId, '2024-12-02', $peopleCount);

        // Assert
        $this->assertTrue($result);
    }

    public function test_location_does_not_have_enough_capacity()
    {
        // Arrange
        $location = Location::factory()->create(['name' => 'test', 'capacity' => 30]);
        $slot = TimeSlot::factory()->create([
            'location_id' => $location->id,
            'day_of_week' => 1,
            'start_time' => '10:00:00',
            'end_time' => '11:00:00',
            'cost_per_hour' => 10,
        ]);

        $booking1 = Booking::factory()->create([
            'location_id' => $location->id,
            'date' => '2024-12-02',
            'people_count' => 25,
        ]);
        $booking1->timeSlots()->attach([$slot->id]);

        $booking2 = Booking::factory()->create([
            'location_id' => $location->id,
            'date' => '2024-12-02',
            'people_count' => 5,
        ]);
        $booking2->timeSlots()->attach([$slot->id]);
        $peopleCount = 10;

        $useCase = new HaveSlots();

        var_dump($slot->bookings->count());

        // Act
        $result = $useCase->execute($location->id, $slot->id, '2024-12-02', $peopleCount);

        // Assert
        $this->assertFalse($result);
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
