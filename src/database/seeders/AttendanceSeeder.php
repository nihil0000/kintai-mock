<?php

namespace Database\Seeders;

use App\Models\Attendance;
use Illuminate\Database\Seeder;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Sequence;
use Carbon\Carbon;
use Carbon\CarbonPeriod;

class AttendanceSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $users = User::all();

        foreach ($users as $user) {
            // 1. Get dates from 2 months ago until yesterday
            $period = CarbonPeriod::create(
                Carbon::now()->subMonths(2)->startOfDay(),
                Carbon::yesterday()->endOfDay()
            );

            // 2. Shuffle and take 40 random dates
            $dates = collect($period)
                ->map(fn($date) => ['date' => $date->format('Y-m-d')])
                ->shuffle()
                ->take(40)
                ->toArray();

            // 3. Use Sequence to assign dates and create 40 records with Factory
            Attendance::factory()
                ->count(40)
                ->state(new Sequence(...$dates))
                ->create([
                    'user_id' => $user->id,
                ]);
        }
    }
}
