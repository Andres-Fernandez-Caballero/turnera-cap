<?php

namespace App\Filament\Resources\BookingResource\Pages;

use App\Core\UseCases\Locations\HaveSlots;
use App\Filament\Resources\BookingResource;
use App\Models\Booking;
use Filament\Actions;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\CreateRecord;
use Filament\Support\Exceptions\Halt;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
use Mockery\Exception;

class CreateBooking extends CreateRecord
{
    protected static string $resource = BookingResource::class;

    protected function afterCreate(): void
    {
        try{
            //
            $data = $this->form->getState()['data'] ?? [];
            $slotsIds = $this->form->getModelInstance()->timeSlots->pluck('id');
            $locationId = $this->form->getState()['location_id'] ?? null;
            $date = $this->form->getState()['date'] ?? null;
            $peopleCount = $this->form->getState()['people_count'] ?? null;

            // Validar datos requeridos
            if (!$locationId || !$date || !$peopleCount || empty($slotsIds)) {
                $this->halt();
            }
            foreach ($slotsIds as $slotId) {
                $isAvailable = app(HaveSlots::class)->execute(
                    (int)$locationId,
                    (int)$slotId,
                    $date,
                    (int)$peopleCount
                );

                if (!$isAvailable) {
                    $this->halt();
                }
            }
        }catch (Halt $e){
            Booking::delete($this->form->getModelInstance()->id);
        // Obtener datos necesarios
            Notification::make()
                ->danger()
                ->title('No se pudieron realizar la reserva')
                ->body('No hay Cupos disponibles para la fecha y horario seleccionado.')
                ->send();
        }
    }

}
