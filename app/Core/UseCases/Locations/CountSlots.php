<?php

namespace App\Core\UseCases\Locations;

use App\Models\Booking;

class CountSlots
{
    public function execute($timeSlot_id, $date): mixed
    {
        $peopleCount = Booking::where('date', $date)
            ->whereHas('timeSlots', function ($query) use ($timeSlot_id) {
                $query->where('time_slots.id', $timeSlot_id);
            })
            ->sum('people_count');

        return (int)$peopleCount;
    }
}
