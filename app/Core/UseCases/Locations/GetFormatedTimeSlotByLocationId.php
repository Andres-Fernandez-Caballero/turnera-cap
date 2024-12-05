<?php

namespace App\Core\UseCases\Locations;

use App\Models\Location;
use App\Models\TimeSlot;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use InvalidArgumentException;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\Log;

class GetFormatedTimeSlotByLocationId
{
    private CountSlots $countSlots;

    public function __construct(CountSlots $countSlots)
    {
        $this->countSlots = $countSlots;
    }

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

            
            $locationCapacity = Location::where('id', $locationId)->value('capacity');
            Log::info('capacidad total', [$locationCapacity]);
            // Retornar en formato esperado
            return $timeSlots
    ->filter(function ($timeSlot) use ($date, $locationCapacity) {
        // Filtrar solo los timeSlots que cumplan con la capacidad
        return $this->countSlots->execute($timeSlot->id, $date) < $locationCapacity;
    })
    ->mapWithKeys(function ($timeSlot) {
        // Formatear los tiempos a "H:i" (hora:minutos)
        $startTime = Carbon::parse($timeSlot->start_time)->format('H:i');
        $endTime = Carbon::parse($timeSlot->end_time)->format('H:i');
        
        // Mapear los timeSlots filtrados al formato deseado
        return [
            //$timeSlot->id => "de {$startTime} a {$endTime}",
            $timeSlot->id => "{$startTime}",
        ];
    })
    ->toArray();
            
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
