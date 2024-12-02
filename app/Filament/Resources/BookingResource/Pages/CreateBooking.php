<?php

namespace App\Filament\Resources\BookingResource\Pages;

use App\Core\UseCases\Locations\HaveSlots;
use App\Filament\Resources\BookingResource;
use App\Models\Booking;
use Filament\Actions;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\CreateRecord;

class CreateBooking extends CreateRecord
{
    protected static string $resource = BookingResource::class;

    // Función antes de crear la reserva
    protected function beforeCreate(): void
    {
        // Obtener los datos del formulario
        $data = $this->form->getState()['data'] ?? [];
        $slotsIds = $this->form->getState()['timeSlots'] ?? [];
        $locationId = $this->form->getState()['location_id'] ?? null;
        $date = $this->form->getState()['date'] ?? null;
        $peopleCount = $this->form->getState()['people_count'] ?? null;
        // Validar campos requeridos
        if (!$locationId || !$date || !$peopleCount || empty($slotsIds)) {
            // Mostrar notificación de error
            Notification::make()
                ->danger()
                ->title('No se pudieron realizar la reserva')
                ->body('Por favor, rellene todos los campos requeridos.')
                ->send();

            // Detener la ejecución sin continuar con la creación
            $this->halt();
            return; // Esto previene cualquier flujo adicional
        }

        // Verificar disponibilidad de los slots
        foreach ($slotsIds as $slotId) {
            $isAvailable = app(HaveSlots::class)->execute(
                (int)$locationId,
                (int)$slotId,
                $date,
                (int)$peopleCount
            );

            if (!$isAvailable) {
                // Mostrar notificación de error si no hay disponibilidad
                Notification::make()
                    ->danger()
                    ->title('No se pudieron realizar la reserva')
                    ->body('No hay Cupos disponibles para la fecha y horario seleccionado.')
                    ->send();

                // Detener la ejecución sin continuar con la creación
                $this->halt();
                return; // Esto previene cualquier flujo adicional
            }
        }
    }

    // Función después de crear la reserva
    protected function afterCreate(): void
    {
        // Asociar los slots seleccionados a la reserva creada
        $this->form->getModelInstance()->timeSlots()->attach($this->form->getState()['timeSlots']);
    }
}
