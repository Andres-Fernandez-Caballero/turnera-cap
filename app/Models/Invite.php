<?php

namespace App\Models;

use App\Models\Traits\HasCapitalizeLastName;
use App\Models\Traits\HasCapitalizeName;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Invite extends Model
{
    use HasCapitalizeName, HasCapitalizeLastName;
    
    protected $fillable = [
        'name','last_name','dni', 'booking_id'
    ];

    public function booking(): BelongsTo
    {
        return $this->belongsTo(Booking::class);
    }

}
