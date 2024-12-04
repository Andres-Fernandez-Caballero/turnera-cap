<?php

namespace App\Models\Payments\Traits;

use App\Models\Payments\Enums\PaymentStatus;
use App\Models\Payments\Payment;
use Illuminate\Database\Eloquent\Relations\MorphOne;

trait HasPayment
{
    public function payment(): MorphOne
    {
        return $this->morphOne(Payment::class,"payable");
    }    

    public function getPaymentStatusAttribute(): PaymentStatus
    {
        if($this->payment == null) return PaymentStatus::PENDING;
        return $this->payment->status;
    }
}