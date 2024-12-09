<?php

namespace App\Http\Controllers;

use App\Models\Booking;
use App\Models\Payments\Enums\PaymentStatus;
use App\Models\Payments\Payment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class MercadoPagoController extends Controller
{

    public function webhooks(Request $request)
    {
        Log::info('Webhook recibido:', context: $request->all());
        Log::info('transaccion id:', context: [$request->input('id')]);
        
        if ($request->input('type') === 'payment') {
            $dataId = $request->input('data.id');

            if ($dataId) {
                $url = "https://api.mercadopago.com/v1/payments/{$dataId}";

                $httpResponse = Http::withHeaders([
                    'Content-Type' => 'application/json',
                    'Authorization' => 'Bearer ' . env('MERCADOPAGO_ACCESS_TOKEN'),
                ])->get($url);

                if ($httpResponse->ok() && isset($httpResponse->json()['external_reference'])) {
                    $status = $httpResponse->json()['status'];
                    Log::info('datos de pago', [$httpResponse->json()]);

                    switch ($status) {
                        case 'approved':
                            $this->accept($dataId, $httpResponse);
                            break;
                        case 'rejected':
                            $this->reject($dataId, $httpResponse);
                            break;
                        case 'pending':
                            $this->pending();
                            break;
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

    protected function accept($dataId, $httpResponse): void
    {
        $reference = $httpResponse->json()['external_reference'];
        $payment = Payment::where('reference', $reference)->first();

        if ($payment) {
            try {
                DB::beginTransaction();
                $payment->status = PaymentStatus::APPROVED;
                $payment->payment_code = $dataId;
                $payment->save();

                Log::info('Pago actualizado correctamente:', ['payment' => $payment]);
                DB::commit();
            } catch (\Exception $e) {
                DB::rollBack();
                Log::error('PAYMENT_ERROR_APPROVED_WEBHOOK', [$e->getMessage()]);
            }
        } else {
            Log::error('No se encontró un pago con la referencia proporcionada.', ['reference' => $reference]);
        }
    }

    protected function reject($dataId, $httpResponse): void
    {
        $reference = $httpResponse->json()['external_reference'];
        $payment = Payment::where('reference', $reference)->first();

        if ($payment) {
            try {
                DB::beginTransaction();
                $payment->status = PaymentStatus::REJECTED;
                //$payment->payment_code = $dataId;
                $payment->save();

                Log::info('Pago rechazado:', ['payment' => $payment]);
                DB::commit();
            } catch (\Exception $e) {
                DB::rollBack();
                Log::error('PAYMENT_ERROR_APPROVED_WEBHOOK', [$e->getMessage()]);
            }
        } else {
            Log::error('No se encontró un pago con la referencia proporcionada.', ['reference' => $reference]);
        }
    }

    protected function pending():void
    {
        // logic here
    }
}
