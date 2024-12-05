<?php

namespace App\Observers;

use App\Models\Invite;

class InviteObserver
{
    /**
     * Handle the Invite "created" event.
     */
    public function created(Invite $invite): void
    {
        $booking= $invite->booking;
        if(! $booking)  throw new \Exception("No se puede crear la reserva porque no existe un libro asociado.");
        $booking->people_count++;
        $booking->save();
    }

    /**
     * Handle the Invite "updated" event.
     */
    public function updated(Invite $invite): void
    {
        //
    }

    /**
     * Handle the Invite "deleted" event.
     */
    public function deleted(Invite $invite): void
    {
        $booking= $invite->booking;
        if(! $booking) 
            throw new \Exception("No se puede eliminar la invitación porque no existe un libro asociado.");
        $booking->people_count--;
        $booking->save;
    }
        

    /**
     * Handle the Invite "restored" event.
     */
    public function restored(Invite $invite): void
    {
        //
    }

    /**
     * Handle the Invite "force deleted" event.
     */
    public function forceDeleted(Invite $invite): void
    {
        //
    }
}
