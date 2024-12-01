<?php
namespace App\Core\UseCases\Locations;

use App\Models\TimeSlot;
use Carbon\Carbon;

class GetTimeSlotsByLocationId
{
    public function execute(int $locationId, string $date): array
    {
        try {
            // Validar formato de la fecha
            if (!Carbon::hasFormat($date, 'Y-m-d')) {
                throw new \InvalidArgumentException('La fecha debe tener el formato Y-m-d.');
            }

            $dayOfWeek = Carbon::createFromFormat('Y-m-d', $date)->dayOfWeek;

            // Obtener TimeSlots
            $timeSlots = TimeSlot::where('is_active', true)
                ->where('day_of_week', 1)
                ->where('location_id', $locationId)
                ->get();

            if ($timeSlots->isEmpty()) {
                return [];
            }

            return $timeSlots->mapWithKeys(function ($timeSlot) {
                return [
                    $timeSlot->id => "de {$timeSlot->start_time} a {$timeSlot->end_time}"
                ];
            })->toArray();
        } catch (\Exception $e) {
            // Manejo de errores
            return ['error' => $e->getMessage()];
        }
    }
}
