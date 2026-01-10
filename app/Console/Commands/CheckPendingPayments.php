<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Payments\Payment;
use App\Enums\FormStatus;
use App\Models\Payments\Enums\PaymentStatus;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class CheckPendingPayments extends Command
{
    protected $signature = 'payments:check-pending';
    protected $description = 'Verifica pagos pendientes buscando por ID o Referencia';

    public function handle()
    {
        $this->info('=== Iniciando Recuperación de Pagos ===');

        // 1. Buscamos pagos NO aprobados de los últimos 10 días
        $pendingPayments = Payment::where('status', '!=', PaymentStatus::APPROVED)
            ->where('created_at', '>=', Carbon::now()->subDays(60)) 
            ->get();

        $count = $pendingPayments->count();
        $this->info("Analizando {$count} pagos pendientes en DB local...");

        foreach ($pendingPayments as $payment) {
            $this->line("------------------------------------------------");
            $this->info("Revisando: {$payment->reference}");

            $mpStatus = null;
            $mpId = $payment->payment_code;
            $foundData = null;

            // CASO A: Tenemos el ID de MP guardado
            if ($mpId && is_numeric($mpId)) {
                $this->comment("-> Buscando por ID: {$mpId}");
                $response = Http::withHeaders(['Authorization' => 'Bearer ' . env('MERCADOPAGO_ACCESS_TOKEN')])
                    ->get("https://api.mercadopago.com/v1/payments/{$mpId}");
                
                if ($response->ok()) {
                    $foundData = $response->json();
                }
            } 
            
            // CASO B: No tenemos ID (webhook falló), buscamos por REFERENCIA
            if (!$foundData) {
                $this->comment("-> ID no encontrado o inválido. Buscando por REFERENCIA externa...");
                $response = Http::withHeaders(['Authorization' => 'Bearer ' . env('MERCADOPAGO_ACCESS_TOKEN')])
                    ->get("https://api.mercadopago.com/v1/payments/search", [
                        'external_reference' => $payment->reference,
                        'status' => 'approved' // Buscamos solo si hay alguno aprobado
                    ]);

                if ($response->ok() && count($response->json()['results']) > 0) {
                    // Tomamos el último pago aprobado con esa referencia
                    $foundData = $response->json()['results'][0]; 
                    $this->info("¡Pago encontrado por referencia!");
                }
            }

            // PROCESAR RESULTADO
            if ($foundData) {
                $mpStatus = $foundData['status'];
                $mpId = $foundData['id'];
                
                $this->info("-> Estado en MercadoPago: [{$mpStatus}]");

                if ($mpStatus === 'approved') {
                    // Actualizamos DB
                    $payment->status = PaymentStatus::APPROVED;
                    $payment->payment_code = $mpId; // Guardamos el ID que nos faltaba
                    $payment->save();
                    
                    // IMPORTANTE: Aquí deberías ejecutar la lógica de asignación (Skater, Juez, etc.)
                    // Si tienes esa lógica aislada, llámala aquí.
                    // $this->triggerApprovalActions($payment); 

                    Log::info("Pago {$payment->reference} recuperado y aprobado manualmente.");
                    $this->info("✅ PAGO APROBADO Y SINCRONIZADO.");
                } else {
                    $this->error("El pago existe pero no está aprobado (Estado: {$mpStatus})");
                }
            } else {
                $this->error("❌ No se encontró ningún pago en MP para esta referencia.");
            }
        }

        $this->info('=== Verificación finalizada ===');
    }
}