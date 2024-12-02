<?php

namespace App\Models\Payments\Traits;

use App\Models\Payments\Payment;
use Illuminate\Database\Eloquent\Relations\HasMany;

trait HasPayments
{
    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class);
    }
}