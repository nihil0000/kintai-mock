<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Enums\AttendanceStatus;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Attendance>
 */
class AttendanceFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $clockIn = $this->faker->dateTimeBetween('08:30:00', '09:30:00');
        $clockOut = (clone $clockIn)->modify('+9 hours');

        return [
            'user_id'   => null,
            'date'      => null,
            'clock_in'  => $clockIn,
            'clock_out' => $clockOut,
            'status'    => AttendanceStatus::AfterWork,
            'note'      => $this->faker->optional()->sentence(),
        ];
    }
}
