<?php

namespace App\Core\UseCases\Locations;

use App\Models\Location;

class HaveSlots
{
    public function execute(int $locationId, $slot_id, date $date): bool
    {
        $location = Location::findOrFail($locationId);
        $capacity = $location->capacity;



        $currentCapacity = $location->bookings()->where('date', $date)
            -timeSlots()->where('id', $slot_id)->first()->people_count;
        $slot = TimeSlot::findOrFail($slot_id);

        if (!$slot->is_active) {
            return false;
        }

        $people_in_slot = $location->bookings()
            ->where('date', $date)
            ->sum('people_count');

        return $people_in_slot <= $capacity;

        $persons = $location->timeSlots()

            ->where('id', $slot_id)
            ->sum('people_count');
        return true;
    }

}
