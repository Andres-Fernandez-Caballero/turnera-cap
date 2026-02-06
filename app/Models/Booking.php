<?php

namespace App\Models;

use App\Models\Payments\Traits\HasPayment;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Booking extends Model
{
    /** @use HasFactory<\Database\Factories\BookingFactory> */
    use HasFactory;
    use HasPayment;

    protected $fillable = [
        'location_id',
        'user_id',
        'date',
        'people_count', 
        'check_in_at',
    ];

    protected $casts = [
        'check_in_at' => 'datetime',
    ];

    protected static function boot()
    {
        parent::boot();

        static::deleting(function ($booking) {            
            if ($booking->payment()->exists()) {
                $booking->payment()->delete();
            }
            // Puedes lanzar una excepción si quieres impedir la eliminación.
            // throw new \Exception('No se puede eliminar este booking.');
        });
    }

    public function location(): BelongsTo
    {
        return $this->belongsTo(Location::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function timeSlots(): BelongsToMany
    {
        return $this->belongsToMany(TimeSlot::class);
    }

    public function invites(): HasMany
    {
        return $this->hasMany(Invite::class);
    }

    public function getPeopleCountAttribute(): int
    {
        return $this->invites()->count();
    }
}
