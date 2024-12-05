<?php

namespace Database\Factories;

use App\Models\Location;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Booking>
 */
class BookingFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'location_id' => Location::factory(),
            'user_id' => User::factory(),
            'date' => Carbon::now()->format('Y-m-d'),
            'people_count' => $this->faker->numberBetween(1, 20),
        ];
    }
}
