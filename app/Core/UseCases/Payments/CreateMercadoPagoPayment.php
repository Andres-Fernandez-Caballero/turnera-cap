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
    ): Preference {
        try {
            $client = new PreferenceClient();
            // Generar referencia única
            $timestamp = time();
            $externalReference = "mercado-pago_{$user->id}_{$timestamp}";

            Log::info('Mercado Pago Payment Creation Request', [
                'title' => $title,
                'description' => $description,
                'amount' => $amount,
                'currency' => $currency,
                'reference' => $externalReference,
                'metadata' => $metadata,
                'hook' => route('payments.mercadopago.webhooks')
            ]);
    
            // URL base
            $url = env('APP_URL', 'http://localhost');

            // Crear preferencia de pago
            $preference = $client->create([
                'external_reference' => $externalReference,
                
                'items' => [
                    [
                        'title' => $title,
                        'quantity' => 1,
                        'unit_price' => $amount,
                        'currency' => $currency,
                    ],
                ],

                'metadata' => $metadata,
                'payer' => [
                    'email' => $user->email,
                    'name' => $user->name,
                    'surname' => $user->last_name,
                    'identification' => [
                        'type' => 'DNI',
                        'number' => $user->dni,
                    ],
                ],
                'notification_url' => route('payments.mercadopago.webhooks'),
                'auto_return' => 'approved',
                'back_urls' => [
                    'success' => "{$url}/success",
                    'failure' => "{$url}/failure",
                    'pending' => "{$url}/pending",
                ],
            ]);

            // Validar que la preferencia se haya creado correctamente
            if (!$preference || !isset($preference->init_point)) {
                Log::error('Error creating Mercado Pago preference: Preference is null or missing init_point', ['response' => $preference]);
                throw new PaymentException(
                    'Error al crear la preferencia de pago.',
                    500,
                    PaymentMethod::MERCADO_PAGO
                );
            }

            // Registrar el pago en la base de datos
            Payment::create([
                'user_id' => $user->id,
                'payment_method' => PaymentMethod::MERCADO_PAGO,
                'currency' => $currency,
                'reference' => $externalReference,
                'amount' => $amount,
                'description' => $description,
                'title' => $title,
            ]);

            // Log del init_point generado
            Log::info('Mercado Pago init_point', [
                'init_point' => $preference->init_point,
                'sandbox_init_point' => $preference->sandbox_init_point ?? null,
            ]);

            return $preference;
        } catch (\Exception $e) {
            // Log del error y lanzamiento de excepción personalizada
            Log::error('Error creating Mercado Pago payment', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            throw new PaymentException(
                'Error al crear la preferencia de pago.',
                490,
                PaymentMethod::MERCADO_PAGO,
                [$e->getMessage()]
            );
        }
    }
}
