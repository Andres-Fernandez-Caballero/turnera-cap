<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\MercadoPagoController;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\File;        // <--- Agregar esto
use Illuminate\Support\Facades\Response;    // <--- Agregar esto

// Webhook para Mercado Pago (POST)
Route::post('payments/webhooks/mercadopago', [MercadoPagoController::class, 'webhooks'])
    ->name('payments.mercadopago.webhooks');

// user routes
Route::prefix("/auth")->group(function () {
    Route::post('/register', [AuthController::class,'register'])->name('api.auth.register');
    Route::post('/login', [AuthController::class,'login'])->name('api.auth.login');
    Route::post('/logout', [AuthController::class,'logout'])
    ->middleware('auth:sanctum')
    ->name('api.auth.logout');    
});

// NUEVO: Ruta para servir imágenes cuando storage:link no funciona
Route::get('/storage-proxy/{path}', function ($path) {
    // Buscamos dentro de storage/app/public/
    $fullPath = storage_path("app/public/" . $path);

    if (!File::exists($fullPath)) {
        abort(404);
    }

    $file = File::get($fullPath);
    $type = File::mimeType($fullPath);

    return Response::make($file, 200)->header("Content-Type", $type);
})->where('path', '.*');

// Public routes
Route::get('locations/{id}/availability',[\App\Http\Controllers\LocationApiController::class, 'getAvailability'])->name('locations.availability.get');

Route::apiResource('locations', \App\Http\Controllers\LocationApiController::class)
->except('destroy','store','update');

Route::apiResource('bookings', \App\Http\Controllers\BookingApiController::class);
Route::post('bookings/{id}/check-in', [\App\Http\Controllers\BookingApiController::class, 'checkIn'])
    ->name('bookings.check-in');

// protected routes
Route::middleware('auth:sanctum')->group(function () {
    // routes to protect
});
