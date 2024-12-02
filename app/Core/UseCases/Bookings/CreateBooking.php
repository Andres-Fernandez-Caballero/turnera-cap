<?php

namespace App\Core\UseCases\Bookings;

use App\Core\UseCases\Locations\HaveSlots;
use App\Models\Booking;
use App\Models\Location;

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
        $people_count
        ){
        
            $location = Location::findOrFail( $location_id );

            if($location->timeSlots->where('is_active', true)->isEmpty()) 
                throw new \Exception('No se pueden crear reservas porque la locación no tiene horarios disponibles.');
        
            if( $location->timeSlots->whereIn('id', $timeSlots)->where('is_active', true)->count() == 0){
                throw new \Exception('No se pueden crear reservas porque el horario solicitado no está disponible.');
            }

            $slotsAvailables = array_map(fn($slot) => $this->haveSlots->execute(
                (int)$location_id,
                (int)$slot,
                $date,
                (int)$people_count
            ), $timeSlots);
            
            if(in_array(false, $slotsAvailables, true))
                throw new \Exception('No se pueden crear reservas porque el horario solicitado no está disponible.');
            
            $booking = Booking::create([
                'user_id' => $user_id,
                'location_id' => $location_id,
                'date' => $date,
                'people_count' => $people_count,
            ]);
            $booking->timeSlots()->attach($timeSlots);
            $booking->save();
            
            return $booking;
    }
}