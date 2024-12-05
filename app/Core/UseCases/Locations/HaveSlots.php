<?php

namespace App\Core\UseCases\Locations;

use App\Models\Booking;
use App\Models\Location;
use App\Models\TimeSlot;
use Carbon\Carbon;

class HaveSlots
{
    public function execute(int $location_id, int $slot_id, string $date, int $people_count): bool
    {
        $location = Location::findOrFail($location_id);
        $dateCarbon = Carbon::parse($date)->format('Y-m-d');
        
        $people_acumulated = Booking::where('location_id', $location_id)
            ->where('date', $dateCarbon)
            ->whereHas('timeSlots', fn($query) => ($query->where('time_slots.id', $slot_id)))
            ->get()
            ->sum('people_count');
        
        return $people_count <= ( $location->capacity - $people_acumulated );
    }

}
