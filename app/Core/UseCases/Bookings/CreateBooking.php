<?php

namespace App\Core\UseCases\Bookings;

use App\Core\UseCases\Locations\HaveSlots;
use App\Models\Booking;
use App\Models\Invite;
use App\Models\Location;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;

class CreateBooking{
    private HaveSlots $haveSlots;

    public function __construct(HaveSlots $haveSlots){
        $this->haveSlots = $haveSlots;
    }

    public function execute (
        $user_id,
        $location_id, 
        array $timeSlots, 
        string $date, 
        array $invites, // ['name' => string, 'last_name' => string, 'dni' => string ]
        ):Model {
        
            $user = User::findOrFail( $user_id );
            $cloned = array_merge([], $invites);
            $cloned[] =  [
                'name' => $user->name, 
                'last_name' => $user->last_name, 
                'dni' => $user->dni 
            ];


            $location = Location::findOrFail( $location_id );

            if($location->timeSlots->where('is_active', true)->isEmpty()) 
                throw new \Exception('No se pueden crear reservas porque la locación no tiene horarios disponibles.');
        
            if( $location->timeSlots->whereIn('id', $timeSlots)->contains('is_active', false)){
                throw new \Exception('No se pueden crear reservas porque el horario solicitado no está disponible.');
            }

            // devuevle verdadero si hay vacantes para la cantidad de personas solicitadas
            $slotsAvailables = array_map(fn($slot) => $this->haveSlots->execute(
                (int)$location_id,
                (int)$slot,
                $date,
                count($cloned)
            ), $timeSlots);
            
            if(in_array(false, $slotsAvailables, true))
                throw new \Exception('No se pueden crear reservas porque el horario solicitado no está disponible.');
            
            $booking = Booking::create([
                'user_id' => $user_id,
                'location_id' => $location_id,
                'date' => $date,
            ]);

            $booking->invites()->createMany( $cloned );
            $booking->timeSlots()->attach($timeSlots);
            $booking->save();
            
            return $booking;
    }
}