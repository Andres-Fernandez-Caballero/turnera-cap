<?php

namespace App\Http\Controllers;

use App\Core\UseCases\Bookings\CreateBooking;
use App\Core\UseCases\Payments\CreateMercadoPagoPayment;
use App\Models\Booking;
use App\Models\Location;
use App\Models\TimeSlot;
use Illuminate\Http\Request;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Illuminate\Routing\Controllers\Middleware;

class BookingApiController extends Controller implements HasMiddleware
{
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
        CreateBooking $createBooking,
        CreateMercadoPagoPayment $createMercadoPagoPayment ){
        $this->createBooking = $createBooking;
        $this->createMercadoPagoPayment = $createMercadoPagoPayment;
    }

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $user = $request->user();
        $booking = Booking::with('location', 'timeSlots')
            ->get();

        return response()
            ->json($booking, 200);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $user = $request->user();

        

        static::validateRequest( $request, [
            'location_id' => 'required|exists:locations,id',
            'timeSlots' => 'required|array',
            'date' => 'required|date',
            'people_count' => 'required|integer|min:1',
        ] );

        try{
            $booking = $this->createBooking->execute(
                $user->id,
                $request->location_id,
                $request->timeSlots,
                $request->date,
                $request->people_count
            );

            $totalAmount = TimeSlot::whereIn('id', $booking->timeSlots->pluck('id')->toArray())->sum('cost_per_hour');

                $preference = $this->createMercadoPagoPayment->execute(
                "Reserva de pista",
                $user,
                $totalAmount,
                [
                    'booking_id' => $booking->id
                ]
            );
            Log:info('init_point', [$preference->sandbox_init_point]);
            return response()->json(['init_point' => $preference->init_point ], 201);

        }catch(\Exception $e){
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

        static::validateRequest( $request,
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
        $validator = Validator::make($request->all(),$rules);
        if( $validator->fails() ) {
            return response()->json($validator->errors(), 405);
        }
    }
}
