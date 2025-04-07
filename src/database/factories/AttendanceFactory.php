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
        $date = $this->faker->dateTimeBetween('-1 month', 'now')->format('Y-m-d');

        $clockIn = $this->faker->dateTimeBetween("{$date} 08:30:00", "{$date} 09:30:00");
        $clockOut = (clone $clockIn)->modify('+8 hours');

        return [
            'user_id'   => null,
            'date'      => $date,
            'clock_in'  => $clockIn,
            'clock_out' => $clockOut,
            'status'    => AttendanceStatus::LeftWork,
            'note'      => $this->faker->optional()->sentence(),
        ];
    }
}
