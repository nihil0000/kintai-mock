<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Database\Seeders\DatabaseSeeder;
use App\Models\User;
use App\Models\Attendance;
use Tests\TestCase;

class AttendanceTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(DatabaseSeeder::class);
    }

    /**
     * Common helper to create attendance record
     */
    private function createAttendance(User $user, \App\Enums\AttendanceStatus $status)
    {
        return Attendance::create([
            'user_id' => $user->id,
            'date' => now()->toDateString(),
            'clock_in' => now()->format('Y-m-d H:i:s'),
            'status' => $status,
        ]);
    }

    /** @test */
    public function 出勤ボタンが正しく機能する()
    {
        $user = User::where('email', 'general@example.com')->first();
        $this->createAttendance($user, \App\Enums\AttendanceStatus::BeforeWork);

        $this->actingAs($user);

        $response = $this->post(route('attendance.start'));
        $response->assertRedirect('/');

        $this->assertDatabaseHas('attendances', [
            'user_id' => $user->id,
            'status' => \App\Enums\AttendanceStatus::Working->value,
        ]);
    }

    /** @test */
    public function 出勤は一日一回のみできる()
    {
        $user = User::where('email', 'general@example.com')->first();
        $this->createAttendance($user, \App\Enums\AttendanceStatus::AfterWork);

        $this->actingAs($user);

        $response = $this->get(route('attendance.create'));

        $response->assertStatus(200);
        $response->assertDontSee('出勤');
    }

    /** @test */
    public function 出勤時刻が管理画面で確認できる()
    {
        $user = User::where('email', 'general@example.com')->first();
        $attendance = $this->createAttendance($user, \App\Enums\AttendanceStatus::Working);

        $this->actingAs($user);

        $response = $this->get(route('attendance.index'));

        $response->assertStatus(200);
        $response->assertSee(\Carbon\Carbon::parse($attendance->clock_in)->format('H:i'));
    }

    /** @test */
    public function 休憩ボタンが正しく機能する()
    {
        $user = User::where('email', 'general@example.com')->first();
        $this->createAttendance($user, \App\Enums\AttendanceStatus::Working);

        $this->actingAs($user);

        $response = $this->post(route('attendance.start_break'));
        $response->assertRedirect('/');

        $this->assertDatabaseHas('attendances', [
            'user_id' => $user->id,
            'status' => \App\Enums\AttendanceStatus::OnBreak->value,
        ]);
    }

    /** @test */
    public function 休憩は一日に何回でもできる_休憩入()
    {
        $user = User::where('email', 'general@example.com')->first();
        $this->createAttendance($user, \App\Enums\AttendanceStatus::Working);

        $this->actingAs($user);

        // First break
        $this->post(route('attendance.start_break'));
        $this->post(route('attendance.end_break'));

        $response = $this->get(route('attendance.create'));

        $response->assertStatus(200);
        $response->assertSee('休憩入');
    }

    /** @test */
    public function 休憩戻ボタンが正しく機能する()
    {
        $user = User::where('email', 'general@example.com')->first();
        $this->createAttendance($user, \App\Enums\AttendanceStatus::OnBreak);

        $this->actingAs($user);

        $response = $this->post(route('attendance.end_break'));
        $response->assertRedirect('/');

        $this->assertDatabaseHas('attendances', [
            'user_id' => $user->id,
            'status' => \App\Enums\AttendanceStatus::Working->value,
        ]);
    }

    /** @test */
    public function 休憩は一日に何回でもできる_休憩戻()
    {
        $user = User::where('email', 'general@example.com')->first();
        $this->createAttendance($user, \App\Enums\AttendanceStatus::Working);

        $this->actingAs($user);

        // First break
        $this->post(route('attendance.start_break'));
        $this->post(route('attendance.end_break'));

        // Second break
        $this->post(route('attendance.start_break'));

        $response = $this->get(route('attendance.create'));

        $response->assertStatus(200);
        $response->assertSee('休憩戻');
    }

    /** @test */
    public function 休憩時刻が勤怠一覧画面で確認できる()
    {
        $user = User::where('email', 'general@example.com')->first();
        $this->createAttendance($user, \App\Enums\AttendanceStatus::Working);

        $this->actingAs($user);

        $this->post(route('attendance.start_break'));
        $this->post(route('attendance.end_break'));

        $response = $this->get(route('attendance.index'));

        $response->assertStatus(200);
        $response->assertSee('00:00');
    }

    /** @test */
    public function 退勤ボタンが正しく機能する()
    {
        $user = User::where('email', 'general@example.com')->first();
        $this->createAttendance($user, \App\Enums\AttendanceStatus::Working);

        $this->actingAs($user);

        $response = $this->post(route('attendance.end'));
        $response->assertRedirect('/');

        $this->assertDatabaseHas('attendances', [
            'user_id' => $user->id,
            'status' => \App\Enums\AttendanceStatus::AfterWork->value,
        ]);
    }

    /** @test */
    public function 退勤時刻が管理画面で確認できる()
    {
        $user = User::where('email', 'general@example.com')->first();
        $attendance = $this->createAttendance($user, \App\Enums\AttendanceStatus::AfterWork);

        $this->actingAs($user);

        $response = $this->get(route('attendance.index'));

        $response->assertStatus(200);
        $response->assertSee(\Carbon\Carbon::parse($attendance->clock_in)->format('H:i'));
        $response->assertSee($attendance->clock_out ? \Carbon\Carbon::parse($attendance->clock_out)->format('H:i') : '');
    }
}
