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
        Log::info('webhook', [$request]);
        if($request['type'] == 'payment')
        {
            switch ($request['action']) {
                case 'payment.created':
                    $url = 'https://api.mercadopago.com/v1/payments/' . $request['data_id'];

                    $httpResponse  = Http::withHeaders([
                        'Content-Type' => 'application/json',
                        'Authorization' => 'Bearer ' . env('MERCADOPAGO_ACCESS_TOKEN'),
                    ])->get($url);

                    if($httpResponse->ok()){
                        $reference = $httpResponse->json()['reference'];

                        $payment = Payment::where('reference', $reference)->first();
                        if($payment){
                            $payment->status = $httpResponse->json()['status'] == 'approved' ?
                            PaymentStatus::APPROVED
                            : PaymentStatus::REJECTED;

                            $payment->payment_code = $request['data_id'];

                            $payment->save();
                        }else {
                            Log::error('MERCADO_PAGO - Peticion al servidor fallida');
                        }

                    }
                break;
            }
        }
    }
}
