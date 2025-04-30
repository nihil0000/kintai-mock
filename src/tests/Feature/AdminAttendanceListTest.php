<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Database\Seeders\DatabaseSeeder;
use App\Models\User;
use App\Models\Attendance;
use Carbon\Carbon;
use Tests\TestCase;

class AdminAttendanceListTest extends TestCase
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
    public function その日になされた全ユーザーの勤怠情報が正確に確認できる()
    {
        $admin = \App\Models\Admin::where('email', 'admin@example.com')->first();
        $user1 = User::create([
            'name' => 'ユーザーA',
            'email' => 'general_a@eexample.com',
            'password' => 'password',
        ]);
        $user2 = User::create([
            'name' => 'ユーザーB',
            'email' => 'general_b@eexample.com',
            'password' => 'password',
        ]);

        Attendance::create([
            'user_id' => $user1->id,
            'date' => now()->toDateString(),
            'clock_in' => now()->setTime(9, 0),
            'clock_out' => now()->setTime(18, 0),
            'status' => \App\Enums\AttendanceStatus::AfterWork,
        ]);

        Attendance::create([
            'user_id' => $user2->id,
            'date' => now()->toDateString(),
            'clock_in' => now()->setTime(10, 0),
            'clock_out' => now()->setTime(19, 0),
            'status' => \App\Enums\AttendanceStatus::AfterWork,
        ]);

        $this->actingAs($admin, 'admins');

        $response = $this->get(route('admin.attendance.index'));

        $response->assertStatus(200);
        $response->assertSee('ユーザーA');
        $response->assertSee('ユーザーB');
        $response->assertSee('09:00');
        $response->assertSee('10:00');
    }

    /** @test */
    public function 遷移した際に現在の日付が表示される()
    {
        $admin = \App\Models\Admin::where('email', 'admin@example.com')->first();
        $this->actingAs($admin, 'admins');

        $response = $this->get(route('admin.attendance.index'));
        $response->assertStatus(200);
        $response->assertSee(now()->format('Y/m/d'));
    }

    /** @test */
    public function 「前日」を押下した時に前の日の勤怠情報が表示される()
    {
        $admin = \App\Models\Admin::where('email', 'admin@example.com')->first();
        $this->actingAs($admin, 'admins');

        $date = now()->subDay();
        $response = $this->get(route('admin.attendance.index', ['date' => $date->format('Y-m-d')]));

        $response->assertStatus(200);
        $response->assertSee($date->format('Y/m/d'));
    }

    /** @test */
    public function 「翌日」を押下した時に次の日の勤怠情報が表示される()
    {
        $admin = \App\Models\Admin::where('email', 'admin@example.com')->first();
        $this->actingAs($admin, 'admins');

        $date = now()->addDay();
        $response = $this->get(route('admin.attendance.index', ['date' => $date->format('Y-m-d')]));

        $response->assertStatus(200);
        $response->assertSee($date->format('Y/m/d'));
    }
}
