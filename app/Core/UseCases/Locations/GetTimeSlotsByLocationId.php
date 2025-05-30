<?php

namespace App\Core\UseCases\Locations;

use App\Models\Location;
use App\Models\TimeSlot;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use InvalidArgumentException;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\Log;

class GetTimeSlotsByLocationId
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
    
            // Obtener capacidad de la locación
            $locationCapacity = Location::where('id', $locationId)->value('capacity');
            Log::info('Capacidad total', ['capacity' => $locationCapacity]);
    
            // Filtrar y mapear los TimeSlots
            return $timeSlots
                ->filter(function ($timeSlot) use ($date, $locationCapacity) {
                    // Filtrar solo los timeSlots que cumplan con la capacidad
                    return $this->countSlots->execute($timeSlot->id, $date) < $locationCapacity;
                })
                ->map(function ($timeSlot) {
                    // Formatear los tiempos y devolver el formato deseado
                    return [
                        'timeSlot_id' => $timeSlot->id,
                        'startTime' => Carbon::parse($timeSlot->start_time)->format('H:i'),
                    ];
                })
                ->values() // Asegura índices consecutivos en el array resultante
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
