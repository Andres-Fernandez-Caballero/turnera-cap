<?php

namespace App\Core\UseCases\Locations;

use App\Models\TimeSlot;
use Carbon\Carbon;
use InvalidArgumentException;
use Illuminate\Database\QueryException;

class GetTimeSlotsByLocationId
{
public function execute(int $locationId, string $date): array
{
try {
// Validar formato y valor de la fecha
$dateObject = Carbon::createFromFormat('Y-m-d', $date);
if (!$dateObject || $dateObject->format('Y-m-d') !== $date) {
throw new InvalidArgumentException('La fecha debe tener el formato Y-m-d.');
}

$dayOfWeek = $dateObject->dayOfWeek;

// Obtener TimeSlots activos para la locación y el día de la semana
$timeSlots = TimeSlot::where('is_active', true)
->where('day_of_week', $dayOfWeek)
->where('location_id', $locationId)
->get();

// Retornar en formato esperado
return $timeSlots->mapWithKeys(function ($timeSlot) {
return [
$timeSlot->id => "de {$timeSlot->start_time} a {$timeSlot->end_time}"
];
})->toArray();

} catch (InvalidArgumentException $e) {
// Error específico de validación
throw $e;
} catch (QueryException $e) {
// Error de la base de datos
throw new \RuntimeException('Error al consultar la base de datos.');
} catch (\Exception $e) {
// Cualquier otro error inesperado
throw new \RuntimeException('Ocurrió un error inesperado: ' . $e->getMessage());
}
}
}
