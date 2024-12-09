<?php

namespace App\Core\UseCases\Payments;

use App\Models\Payments\Enums\PaymentMethod;
use App\Models\Payments\Enums\PaymentStatus;
use App\Models\Payments\Payment;
use App\Models\User;

class CreateAdminPayment
{
    public function execute( User $user, int $amount, string $title, string $description = '' ):Payment
    {
        $time = time();
        return Payment::create([
            'user_id' => $user->id,
            'payment_method' => PaymentMethod::PAGO_EN_ADMINISTRACION,
            'payment_code' => "CAP-{$user->id}-{$time}",
            'reference' => "CAP-REFERENCE-{$user->id}-{$time}",
            'amount' => $amount,
            'status' => PaymentStatus::APPROVED,
            'title' => $title,
            'description' => $description,
        ]);

    }
}