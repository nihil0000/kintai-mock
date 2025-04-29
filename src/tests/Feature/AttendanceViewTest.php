<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Database\Seeders\DatabaseSeeder;
use App\Models\User;
use App\Models\Attendance;
use Carbon\Carbon;
use Tests\TestCase;

class AttendanceViewTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(DatabaseSeeder::class);
    }

    /** @test */
    public function 現在の日時情報がUIと同じ形式で出力されている()
    {
        $user = User::where('email', 'general@example.com')->first();

        // Login
        $this->actingAs($user);

        $response = $this->get(route('attendance.create'));

        $date = Carbon::now()->format('Y年n月j日') . '（' . ['日', '月', '火', '水', '木', '金', '土'][Carbon::now()->dayOfWeek] . '）';
        $time = Carbon::now()->format('H:i');

        $response->assertSee($date);
        $response->assertSee($time);
    }

    /** @test */
    public function 勤務外の場合勤怠ステータスが正しく表示される()
    {
        $user = User::where('email', 'general@example.com')->first();

        // Create attendance
        Attendance::create([
            'user_id' => $user->id,
            'date' => now()->toDateString(),
            'clock_in' => now()->format('Y-m-d H:i:s'),
            'status' => \App\Enums\AttendanceStatus::BeforeWork,
        ]);

        $this->actingAs($user);

        $response = $this->get(route('attendance.create'));

        $response->assertStatus(200);
        $response->assertSee('勤務外');
    }

    /** @test */
    public function 出勤中の場合勤怠ステータスが正しく表示される()
    {
        $user = User::where('email', 'general@example.com')->first();

        // Create attendance
        Attendance::create([
            'user_id' => $user->id,
            'date' => now()->toDateString(),
            'clock_in' => now()->format('Y-m-d H:i:s'),
            'status' => \App\Enums\AttendanceStatus::Working,
        ]);

        $this->actingAs($user);

        $response = $this->get(route('attendance.create'));

        $response->assertStatus(200);
        $response->assertSee('出勤中');
    }

    /** @test */
    public function 休憩中の場合勤怠ステータスが正しく表示される()
    {
        $user = User::where('email', 'general@example.com')->first();

        // Create attendance
        Attendance::create([
            'user_id' => $user->id,
            'date' => now()->toDateString(),
            'clock_in' => now()->format('Y-m-d H:i:s'),
            'status' => \App\Enums\AttendanceStatus::OnBreak,
        ]);

        $this->actingAs($user);

        $response = $this->get(route('attendance.create'));

        $response->assertStatus(200);
        $response->assertSee('休憩中');
    }

    /** @test */
    public function 退勤済の場合勤怠ステータスが正しく表示される()
    {
        $user = User::where('email', 'general@example.com')->first();

        // Create attendance
        Attendance::create([
            'user_id' => $user->id,
            'date' => now()->toDateString(),
            'clock_in' => now()->format('Y-m-d H:i:s'),
            'status' => \App\Enums\AttendanceStatus::AfterWork,
        ]);

        $this->actingAs($user);

        $response = $this->get(route('attendance.create'));

        $response->assertStatus(200);
        $response->assertSee('退勤済');
    }
}
