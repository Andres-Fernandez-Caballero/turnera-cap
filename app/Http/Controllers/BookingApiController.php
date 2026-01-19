<?php

namespace App\Http\Controllers;

use App\Core\UseCases\Bookings\CreateBooking;
use App\Core\UseCases\Bookings\ListBookingsByUser;
use App\Core\UseCases\Payments\CreateMercadoPagoPayment;
use App\Models\Booking;
use App\Models\Payments\Enums\PaymentMethod;
use App\Models\Payments\Enums\PaymentStatus;
use App\Models\Payments\Payment;
use App\Models\TimeSlot;
use Illuminate\Http\Request;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Support\Facades\Validator;
use Illuminate\Routing\Controllers\Middleware;
use Illuminate\Support\Facades\DB;

class BookingApiController extends Controller implements HasMiddleware
{
    private ListBookingsByUser $listBookingsByUser;
    private CreateBooking $createBooking;
    private CreateMercadoPagoPayment $createMercadoPagoPayment;
    /**
     * Get the middleware that should be assigned to the controller.
     */
    public static function middleware(): array
    {
        return [
            new Middleware('auth:sanctum'),
        ];
    }

    public function __construct(
        ListBookingsByUser $listBookingsByUser,
        CreateBooking $createBooking,
        CreateMercadoPagoPayment $createMercadoPagoPayment
    ) {
        $this->listBookingsByUser = $listBookingsByUser;
        $this->createBooking = $createBooking;
        $this->createMercadoPagoPayment = $createMercadoPagoPayment;
    }

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        try{
            $user = $request->user();        
            $bookings = $this->listBookingsByUser->execute($user);
    
            return response()
                ->json($bookings, 200);
        }catch(\Exception $e){
            return response()->json([
                'message'=> "No se pudieron cargar las reservas, pruebe mas tarde",
            ], 400);
        }
    }

    /**
     * Store a newly created resource in storage.
     */
public function store(Request $request)
{
    try {
        $user = $request->user();

        if (!$user) {
            throw new \Exception('No user found');
        }

        static::validateRequest($request, [
            'location_id' => 'required|exists:locations,id',
            'timeSlots' => 'required|array',
            'invites' => 'array',
            'date' => 'required|date|after_or_equal:today',
            'payment_method' => 'sometimes|string|in:mercado_pago,pago_administracion',
        ]);

        DB::beginTransaction();
        $booking = $this->createBooking->execute(
            $user->id,
            $request->location_id,
            $request->timeSlots,
            $request->date,
            $request->invites
        );

        $totalAmount = TimeSlot::whereIn('id', $booking->timeSlots->pluck('id')->toArray())->sum('cost_per_hour');

        $paymentMethod = $request->payment_method ?? 'mercado_pago';

        // Si es pago en administración, crear pago pendiente sin MercadoPago
        if ($paymentMethod === 'pago_administracion') {
            $payment = Payment::create([
                'user_id' => $user->id,
                'payment_method' => PaymentMethod::PAGO_EN_ADMINISTRACION,
                'amount' => $totalAmount,
                'currency' => 'ARS',
                'status' => PaymentStatus::PENDING,
                'title' => 'Reserva de pista',
                'description' => 'Pago pendiente en administración',
                'reference' => 'admin_' . $user->id . '_' . time(), // <-- Agregar esta línea
            ]);
            $booking->payment()->save($payment);
            
            DB::commit();
            return response()->json([
                'message' => 'Reserva creada. Pendiente de pago en administración.',
                'booking_id' => $booking->id,
            ], 201);
        }

        // Mercado Pago (comportamiento actual)
        $preference = $this->createMercadoPagoPayment->execute(
            "Reserva de pista",
            $user,
            $totalAmount,
            [
                'booking_id' => $booking->id
            ]
        );
        $payment = Payment::find($preference['payment_id']);
        $booking->payment()->save($payment);
        
        if (!$preference) {
            throw new \Exception('Error generando Pago');
        }
        DB::commit();
        return response()
            ->json(['init_point' => env('SANDBOX', false) == false ? $preference['init_point'] : $preference['sandbox_init_point']], 201);
    } catch (\Exception $e) {
        DB::rollBack();
        return response()->json([
            'message' => $e->getMessage(),
        ], 422);
    }
}

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $booking = Booking::with('location')->findOrFail($id);
        return response()->json($booking, 200);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $booking = Booking::findOrFail($id);

        static::validateRequest(
            $request,
            [
                'start_time' => 'sometimes|date|before:end_time',
                'end_time' => 'sometimes|date|after:start_time',
                'people_count' => 'sometimes|integer|min:1',
            ]
        );


        $booking->update($request->all());
        return response()->json($booking, 200);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $booking = Booking::findOrFail($id);
        $booking->delete();

        return response()->json($booking, 204);
    }

    private static function validateRequest(Request $request, array $rules = [])
    {
        $validator = Validator::make($request->all(), $rules);
        if ($validator->fails()) {
            throw new \Illuminate\Validation\ValidationException($validator);
        }
    }
}
