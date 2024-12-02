<?php

namespace App\Models\Payments;

use App\Models\Payments\Enums\PaymentMethod;
use App\Models\Payments\Enums\PaymentStatus;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class Payment extends Model
{
    protected $fillable = [
       'user_id',
       'payment_method',
       'currency',
       'payment_code',
       'reference',
       'amount',
       'status',
       'description',
       'title',
];

protected $casts = [
    'payment_method' => PaymentMethod::class,
    'status' => PaymentStatus::class,
];

public function user(): HasOne
{
    return $this->hasOne(User::class);
}

public function payable(): MorphTo
{
    return $this->morphTo('payable');
}
}
