<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\BreakTime>
 */
class BreakTimeFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $breakStart = $this->faker->dateTimeBetween('today 12:00', 'today 14:00');
        $breakEnd = (clone $breakStart)->modify('+30 minutes');

        return [
            'attendance_id' => null,
            'break_start'   => $breakStart,
            'break_end'     => $breakEnd,
        ];
    }
}
