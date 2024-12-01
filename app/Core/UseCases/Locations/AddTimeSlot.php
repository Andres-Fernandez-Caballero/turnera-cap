<?php

namespace App\Core\UseCases\Locations;

use App\Models\Location;
use App\Models\TimeSlot;

class AddTimeSlot
{
    private ValidateTimeSlot $validateTimeSlot;
    public function __construct(ValidateTimeSlot $validateTimeSlot)
    {
        $this->validateTimeSlot = $validateTimeSlot;
    }

    public function execute(int $locationId, array $data)
    {
        $overlappingSlot = $this->validateTimeSlot->execute($locationId, $data);
        if (!$overlappingSlot) {
            throw new \Exception('El nuevo horario se superpone con un horario existente.');
        }

        return TimeSlot::create(array_merge($data, ['location_id' => $locationId]));
    }
}
