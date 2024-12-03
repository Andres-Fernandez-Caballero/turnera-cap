<?php

namespace App\Core\UseCases\Payments;

use App\Models\Payments\Enums\PaymentMethod;
use App\Models\Payments\Payment;
use App\Models\Payments\PaymentException;
use App\Models\User;
use Illuminate\Support\Facades\Log;
use MercadoPago\Client\Preference\PreferenceClient;
use MercadoPago\Resources\Preference;

class CreateMercadoPagoPayment
{
    public function execute(
        string $title, 
        User $user, 
        float $amount,
        array $metadata = [],
        string $description = '',
        string $currency = 'ARS'
        ):Preference
    {
        $client = new PreferenceClient();
        try{
            $url = env('APP_URL');
            $externalReference = "mercado-pago_".$user->id .'_'.time();        
            $preference = $client->create([
                'external_reference' => $externalReference,
                'items' => [
                        [
                            'title' => $title,
                            'quantity' => 1,
                            'unit_price' => $amount,
                            'currency' => $currency,
                        ]
                    ],
                    'metadata' => $metadata,
                    'payer' => [
                        'email' => $user->email,
                        'name' => $user->name,
                        'surname'=> $user->last_name,
                        "identification" => [
                        "type" => "DNI",
                        "number" => $user->dni
                        /*
                        "phone" => [
                            "area_code" => "11",
                            "number" => "4444-4444"
                        ],
                        "address" => [
                            "zip_code" => "1234",
                            "street_name" => "Falsa",
                            "street_number" => 123
                        ]
                        */
                    ],
                    
                ],
                'notification_url' => route('payments.mercadopago.webhooks'),
                'auto_return' => "approved",
                'back_urls' => [
                    "success" => "{$url}/success", //filament.member.pages.success-payment', ['tenant' => Filament::getTenant()]),
                    "failure" => "{$url}/failure",  // route('filament.member.pages.rejected-payment', ['tenant' => Filament::getTenant()]),
                    "pending" => "{$url}/pending", //"{$url}/payments/pending/mercadopago",
                ],

            ]);

            Payment::create([
                'user_id' => $user->id,
                'payment_method' => PaymentMethod::MERCADO_PAGO,
                'currency'=> $currency,
                'reference' => $externalReference,
                'amount' => $amount,
                'description'=> $description,
                'title' => $title,
            ]);
            Log::info('init_point', [$preference->init_point]);
            return $preference;
        }catch(PaymentException $e){
            throw new PaymentException(
                'Error al crear la preferencia de pago.',
                500,
                PaymentMethod::MERCADO_PAGO,
                [$e->getMessage()]
            );
        }

    }
}