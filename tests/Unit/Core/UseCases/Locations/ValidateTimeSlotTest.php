<?php

namespace Tests\Unit\Core\UseCases\Locations;

use App\Core\UseCases\Locations\ValidateTimeSlot;
use App\Models\Location;
use App\Models\TimeSlot;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ValidateTimeSlotTest extends TestCase
{
    use RefreshDatabase;

    protected ValidateTimeSlot $validateTimeSlot;

    protected function setUp(): void
    {
        parent::setUp();
        $this->validateTimeSlot = new ValidateTimeSlot();
    }


    public function test_it_returns_true_if_no_overlapping_time_slots_exist()
    {
        $location = Location::factory()->create();

        TimeSlot::factory()->create([
            'location_id' => $location->id,
            'day_of_week' => 1,
            'start_time' => '10:00:00',
            'end_time' => '11:00:00',
        ]);

        $data = [
            'day_of_week' => 1,
            'start_time' => '11:00:00',
            'end_time' => '12:00:00',
        ];

        $result = $this->validateTimeSlot->execute($location->id, $data);

        $this->assertTrue($result);
    }

    public function test_it_returns_false_if_time_slots_overlap_partially_on_start()
    {
        $location = Location::factory()->create();

        TimeSlot::factory()->create([
            'location_id' => $location->id,
            'day_of_week' => 1,
            'start_time' => '10:00:00',
            'end_time' => '12:00:00',
        ]);

        $data = [
            'day_of_week' => 1,
            'start_time' => '11:30:00',
            'end_time' => '13:00:00',
        ];

        $result = $this->validateTimeSlot->execute($location->id, $data);

        $this->assertFalse($result);
    }

    public function test_it_returns_false_if_time_slots_overlap_partially_on_end()
    {
        $location = Location::factory()->create();

        TimeSlot::factory()->create([
            'location_id' => $location->id,
            'day_of_week' => 1,
            'start_time' => '10:00:00',
            'end_time' => '12:00:00',
        ]);

        $data = [
            'day_of_week' => 1,
            'start_time' => '09:00:00',
            'end_time' => '10:30:00',
        ];

        $result = $this->validateTimeSlot->execute($location->id, $data);

        $this->assertFalse($result);
    }

    public function test_it_returns_false_if_time_slots_fully_contain_the_new_slot()
    {
        $location = Location::factory()->create();

        TimeSlot::factory()->create([
            'location_id' => $location->id,
            'day_of_week' => 1,
            'start_time' => '10:00:00',
            'end_time' => '14:00:00',
        ]);

        $data = [
            'day_of_week' => 1,
            'start_time' => '11:00:00',
            'end_time' => '13:00:00',
        ];

        $result = $this->validateTimeSlot->execute($location->id, $data);

        $this->assertFalse($result);
    }
}
