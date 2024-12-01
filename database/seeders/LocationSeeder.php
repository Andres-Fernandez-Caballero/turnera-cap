<?php

namespace Database\Seeders;

use App\Models\TimeSlot;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class LocationSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::table('locations')->insert([
            [
                'name' => 'Pista de patinaje',
                'capacity' => 30,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Cancha de tenis',
                'capacity' => 4,
                'created_at' => now(),
                'updated_at' => now(),
            ]
        ]);

        DB::table('time_slots')->insert([
            'location_id' => 1,
            'day_of_week' => 1,
            'start_time' => '10:00',
            'end_time' => '11:00',
            'cost_per_hour' => 1000,
            'is_active' => true,
            'created_at' => now(),
            'updated_at' => now(),

        ],
        [
            'location_id' => 1,
            'day_of_week' => 1,
            'start_time' => '11:00',
            'end_time' => '12:00',
            'cost_per_hour' => 1000,
            'is_active' => true,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

    }
}
