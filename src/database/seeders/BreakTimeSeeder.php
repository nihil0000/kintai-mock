<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Attendance;
use App\Models\BreakTime;

class BreakTimeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $attendances = Attendance::all();

        foreach ($attendances as $attendance) {
            BreakTime::factory()->count(2)->create([
                'attendance_id' => $attendance->id,
            ]);
        }
    }
}
