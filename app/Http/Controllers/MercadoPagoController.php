<?php

namespace App\Http\Controllers;

use App\Models\Payments\Enums\PaymentStatus;
use App\Models\Payments\Payment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class MercadoPagoController extends Controller
{

    public function webhooks(Request $request)
    {
        Log::info('Webhook recibido:', $request->all());

        if ($request->input('type') === 'payment') {
            $dataId = $request->input('data.id');

            if ($dataId) {
                $url = 'https://api.mercadopago.com/v1/payments/' . $dataId;

                $httpResponse = Http::withHeaders([
                    'Content-Type' => 'application/json',
                    'Authorization' => 'Bearer ' . env('MERCADOPAGO_ACCESS_TOKEN'),
                ])->get($url);

                if ($httpResponse->ok() && isset($httpResponse->json()['external_reference'])) {
                    $reference = $httpResponse->json()['external_reference'];

                    $payment = Payment::where('reference', $reference)->first();
                    if ($payment) {
                        $payment->status = $httpResponse->json()['status'] === 'approved'
                            ? PaymentStatus::APPROVED
                            : PaymentStatus::REJECTED;

                        $payment->payment_code = $dataId;
                        $payment->save();

                        Log::info('Pago actualizado correctamente:', ['payment' => $payment]);
                    } else {
                        Log::error('No se encontró un pago con la referencia proporcionada.', ['reference' => $reference]);
                    }
                } else {
                    Log::error('Error al consultar el pago en Mercado Pago.', ['response' => $httpResponse->body()]);
                }
            } else {
                Log::error('El campo data.id está ausente en el webhook.');
            }
        }

        return response()->json(['status' => 'success'], 200);
    }
}
