<?php

namespace App\Core\UseCases\Locations;

use App\Models\Location;

class ValidateTimeSlot
{
public function execute(int $locationId, array $data): bool
{
$location = Location::findOrFail($locationId);

// Verificar superposición de TimeSlots
$overlappingSlot = $location->timeSlots()
->where('day_of_week', $data['day_of_week'])
->where(function ($query) use ($data) {
$query->where(function ($query) use ($data) {
$query->where('start_time', '<', $data['end_time'])
->where('end_time', '>', $data['start_time']);
});
})
->exists();
return !$overlappingSlot;
}
}
