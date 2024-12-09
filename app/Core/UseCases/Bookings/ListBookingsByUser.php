<?php

namespace App\Core\UseCases\Bookings;

use App\Models\Booking;
use App\Models\User;

class ListBookingsByUser
{
    public function execute(User $user)
    {
        return Booking::with('location','timeSlots', 'invites', 'payment')
        ->where('user_id', $user->id)
        ->get();
    }
}