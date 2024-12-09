<?php

namespace App\Filament\Resources\BookingResource\Pages;

use App\Core\UseCases\Locations\HaveSlots;
use App\Core\UseCases\Payments\CreateAdminPayment;
use App\Filament\Resources\BookingResource;
use App\Models\Booking;
use App\Models\Payments\Enums\PaymentMethod;
use App\Models\Payments\Enums\PaymentStatus;
use App\Models\Payments\Payment;
use App\Models\User;
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
        $invites = $this->form->getState()['invites'] ?? [];

        // Validar campos requeridos
        if (!$locationId || !$date ||  empty($slotsIds)) {
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
                count($invites),
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

        $user = User::findOrFail($this->form->getState()['user_id']);


        $this->form->getModelInstance()->invites()->create([
            'name' => $user->name,
            'last_name' => $user->last_name,
            'dni' => $user->dni
        ]);

        $this->form->getModelInstance()->invites()->createMany($this->form->getState()['invites']);

        $payment = app(CreateAdminPayment::class)->execute(
            $user,
            (int)$this->form->getModelInstance()->sum('cost_per_hour'),
            'Reserva de pista',
        );

        $this->form->getModelInstance()->payment()->save($payment);
    }
}
