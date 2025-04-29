<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Database\Seeders\DatabaseSeeder;
use App\Models\User;
use App\Models\Attendance;
use Carbon\Carbon;
use Tests\TestCase;

class AttendanceListTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(DatabaseSeeder::class);
    }

    private function createAttendances(User $user, int $count = 3, ?Carbon $month = null)
    {
        $month = $month ?? now();
        for ($i = 1; $i <= $count; $i++) {
            Attendance::create([
                'user_id' => $user->id,
                'date' => $month->copy()->day($i),
                'clock_in' => $month->copy()->day($i)->setTime(9, 0),
                'clock_out' => $month->copy()->day($i)->setTime(18, 0),
                'status' => \App\Enums\AttendanceStatus::AfterWork,
            ]);
        }
    }

    /** @test */
    public function 自分が行った勤怠情報が全て表示されている()
    {
        $user = User::where('email', 'general@example.com')->first();
        $this->createAttendances($user, 20);

        $this->actingAs($user);

        $response = $this->get(route('attendance.index'));

        $response->assertStatus(200);

        $startOfMonth = now()->startOfMonth();
        $endOfMonth = now()->endOfMonth();
        $daysOfWeek = ['日', '月', '火', '水', '木', '金', '土'];

        for ($date = $startOfMonth; $date->lte($endOfMonth); $date->addDay()) {
            $formattedDate = $date->format('m/d') . '(' . $daysOfWeek[$date->dayOfWeek] . ')';
            $response->assertSee($formattedDate);

            if ($date->day <= 20) {
                // 打刻ありの日（1日〜20日）
                $response->assertSee('09:00');
                $response->assertSee('18:00');
            } else {
                // 打刻なしの日（21日〜月末）
                $response->assertSee('-');
            }
        }
    }

    /** @test */
    public function 勤怠一覧画面に遷移した際に現在の月が表示される()
    {
        $user = User::where('email', 'general@example.com')->first();
        $this->actingAs($user);

        $response = $this->get(route('attendance.index'));

        $response->assertStatus(200);
        $response->assertSee(now()->format('Y/m'));
    }

    /** @test */
    public function 「前月」を押下した時に表示月の前月の情報が表示される()
    {
        $user = User::where('email', 'general@example.com')->first();
        $previousMonth = now()->subMonth();

        $this->actingAs($user);

        $response = $this->get(route('attendance.index', ['month' => $previousMonth->format('Y-m')]));

        $response->assertStatus(200);
        $response->assertSee($previousMonth->format('Y/m'));
    }

    /** @test */
    public function 「翌月」を押下した時に表示月の前月の情報が表示される()
    {
        $user = User::where('email', 'general@example.com')->first();
        $nextMonth = now()->addMonth();

        $this->actingAs($user);

        $response = $this->get(route('attendance.index', ['month' => $nextMonth->format('Y-m')]));

        $response->assertStatus(200);
        $response->assertSee($nextMonth->format('Y/m'));
    }

    /** @test */
    public function 「詳細」を押下すると、その日の勤怠詳細画面に遷移する()
    {
        $user = User::where('email', 'general@example.com')->first();
        $attendance = Attendance::create([
            'user_id' => $user->id,
            'date' => now()->toDateString(),
            'clock_in' => now()->setTime(9, 0),
            'clock_out' => now()->setTime(18, 0),
            'status' => \App\Enums\AttendanceStatus::AfterWork,
        ]);

        $this->actingAs($user);

        $response = $this->get(route('attendance.show', $attendance->id));

        $response->assertStatus(200);
        $response->assertSee($attendance->user->name);
        $response->assertSee(now()->format('n月j日'));
    }
}
