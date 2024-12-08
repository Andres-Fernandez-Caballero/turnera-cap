<?php

namespace Tests\Unit\Core\UseCases\Bookings;

use App\Core\UseCases\Bookings\CreateBooking;
use App\Core\UseCases\Locations\HaveSlots;
use App\Models\Booking;
use App\Models\Location;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Mockery;
use Tests\TestCase;

class CreateBookingTest extends TestCase
{
    use RefreshDatabase;

    public function test_creates_booking_successfully()
{
    $haveSlotsMock = new HaveSlots();
    
    // Datos de prueba
    $user = User::factory()->create();
    $location = Location::factory(['capacity' => 5])->create();

    // Crear los timeSlots
    $timeSlots = $location->timeSlots()->createMany([
        [
            'day_of_week' => 1, // Lunes
            'start_time' => '08:00',
            'end_time' => '09:00',
            'cost_per_hour' => 1000,
            'is_active' => true,
        ],
    ]);

    // Obtener los IDs generados
    $timeSlotIds = $timeSlots->pluck('id')->toArray();

    $date = '2024-12-10';
    $invites = [
        ['name' => 'John', 'last_name' => 'Doe', 'dni' => '123456'],
        ['name' => 'Jane', 'last_name' => 'Smith', 'dni' => '789012'],
    ];

    // Instancia del caso de uso
    $useCase = new CreateBooking($haveSlotsMock);

    // Ejecuta el caso de uso
    $booking = $useCase->execute(
        $user->id,
        $location->id,
        $timeSlotIds, // Usar IDs generados dinámicamente
        $date,
        $invites
    );

    // Aserciones
    $this->assertInstanceOf(Booking::class, $booking);
    $this->assertEquals($user->id, $booking->user_id);
    $this->assertEquals($location->id, $booking->location_id);
    $this->assertEquals($date, $booking->date);

    // Verifica que se crearon los invites
    $this->assertCount(3, $booking->invites);
    $this->assertDatabaseHas('invites', ['name' => 'John', 'dni' => '123456']);
    $this->assertDatabaseHas('invites', ['name' => 'Jane', 'dni' => '789012']);
    $this->assertDatabaseHas('invites', ['name' => $user->name, 'dni' => $user->dni]);

    // Verifica que los time slots fueron asociados
    foreach ($timeSlotIds as $slotId) {
        $this->assertTrue($booking->timeSlots->contains('id', $slotId));
    }
}


    public function test_throws_exception_when_location_has_no_active_slots()
    {
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('No se pueden crear reservas porque el horario solicitado no está disponible.');

        $haveSlotsMock = new HaveSlots();

        $user = User::factory()->create();
        $location = Location::factory()->create();

        // Locación sin time slots activos
        $location->timeSlots()->createMany([
            [
                'day_of_week' => 1, // Lunes
                'start_time' => '08:00',
                'end_time' => '09:00',
                'cost_per_hour' => 1000,
                'is_active' => true,
            ],
            [
                'day_of_week' => 1, // Lunes
                'start_time' => '09:00',
                'end_time' => '10:00',
                'cost_per_hour' => 1000,
                'is_active' => false,
            ],
        ]
        );



        $useCase = new CreateBooking($haveSlotsMock);

        $useCase->execute(
            $user->id, 
            $location->id, 
            $location->timeSlots->pluck('id')->toArray(),
            '2024-12-09',
            []
        );
    }

    public function test_throws_exception_when_requested_slots_are_unavailable()
    {
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('No se pueden crear reservas porque la locación no tiene horarios disponibles.');
    
        $haveSlotsMock = new HaveSlots();
        
        $user = User::factory()->create();
        $location = Location::factory(['capacity' => 1])->create();
    
        $useCase = new CreateBooking($haveSlotsMock);
        $timeSlotIds = [1,2,3,4,5,6,];
        $useCase->execute(
            $user->id,
            $location->id,
            $timeSlotIds, // Usar IDs generados dinámicamente
            '2024-12-10',
            []
        );
    }
    
}
