<?php

namespace Database\Factories;

use App\Models\Location;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\TimeSlot>
 */
class TimeSlotFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'location_id' => Location::factory()->create()->id,
            'day_of_week' => $this->faker->numberBetween(0, 6),
            'start_time' => $this->faker->dateTimeBetween('now', '+1 day')->format('H:i:s'),
            'end_time' => $this->faker->dateTimeBetween('now', '+1 day')->format('H:i:s'),
            'cost_per_hour' => $this->faker->numberBetween(10, 1000),
        ];
    }
}
