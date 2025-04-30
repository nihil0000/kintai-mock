<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Database\Seeders\DatabaseSeeder;
use Tests\TestCase;
use App\Models\User;
use App\Models\Admin;
use App\Models\Attendance;
use Carbon\Carbon;

class AdminAttendanceDetailTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(DatabaseSeeder::class);
    }

    /** @test */
    public function 勤怠詳細画面に表示されるデータが選択したものになっている()
    {
        $admin = Admin::where('email', 'admin@example.com')->first();
        $user = User::where('email', 'general@example.com')->first();

        $attendance = Attendance::create([
            'user_id' => $user->id,
            'date' => now()->toDateString(),
            'clock_in' => Carbon::today()->setTime(9, 0),
            'clock_out' => Carbon::today()->setTime(18, 0),
            'status' => \App\Enums\AttendanceStatus::AfterWork,
        ]);

        $this->actingAs($admin, 'admins');

        $response = $this->get(route('attendance.show', $attendance->id));

        $response->assertStatus(200);
        $response->assertSee('general');
        $response->assertSee(now()->format('n月j日'));
        $response->assertSee('09:00');
        $response->assertSee('18:00');
    }

    /** @test */
    public function 出勤時間が退勤時間より後になっている場合、エラーメッセージが表示される()
    {
        $admin = Admin::where('email', 'admin@example.com')->first();
        $user = User::where('email', 'general@example.com')->first();

        $attendance = Attendance::create([
            'user_id' => $user->id,
            'date' => now()->toDateString(),
            'clock_in' => now()->setTime(9, 0),
            'clock_out' => now()->setTime(18, 0),
            'status' => \App\Enums\AttendanceStatus::AfterWork,
        ]);

        $this->actingAs($admin, 'admins');

        $response = $this->post(route('attendance_correction.store', $attendance->id), [
            'requested_clock_in' => '19:00',
            'requested_clock_out' => '18:00',
            'note' => 'test note',
        ]);

        $response->assertSessionHasErrors(['requested_clock_in']);
    }

    /** @test */
    public function 休憩開始時間が退勤時間より後になっている場合、エラーメッセージが表示される()
    {
        $admin = Admin::where('email', 'admin@example.com')->first();
        $user = User::where('email', 'general@example.com')->first();

        $attendance = Attendance::create([
            'user_id' => $user->id,
            'date' => now()->toDateString(),
            'clock_in' => now()->setTime(9, 0),
            'clock_out' => now()->setTime(18, 0),
            'status' => \App\Enums\AttendanceStatus::AfterWork,
        ]);

        $this->actingAs($admin, 'admins');

        $response = $this->post(route('attendance_correction.store', $attendance->id), [
            'requested_clock_in' => '09:00',
            'requested_clock_out' => '18:00',
            'requested_breaks' => [
                'start' => ['19:00'],
                'end' => ['20:00'],
            ],
            'note' => 'test note',
        ]);

        $response->assertSessionHasErrors(['requested_breaks.start.0']);
    }

    /** @test */
    public function 休憩終了時間が退勤時間より後になっている場合、エラーメッセージが表示される()
    {
        $admin = Admin::where('email', 'admin@example.com')->first();
        $user = User::where('email', 'general@example.com')->first();

        $attendance = Attendance::create([
            'user_id' => $user->id,
            'date' => now()->toDateString(),
            'clock_in' => now()->setTime(9, 0),
            'clock_out' => now()->setTime(18, 0),
            'status' => \App\Enums\AttendanceStatus::AfterWork,
        ]);

        $this->actingAs($admin, 'admins');

        $response = $this->post(route('attendance_correction.store', $attendance->id), [
            'requested_clock_in' => '09:00',
            'requested_clock_out' => '18:00',
            'requested_breaks' => [
                'start' => ['12:00'],
                'end' => ['19:00'],
            ],
            'note' => 'test note',
        ]);

        $response->assertSessionHasErrors(['requested_breaks.end.0']);
    }

    /** @test */
    public function 備考欄が未入力の場合のエラーメッセージが表示される()
    {
        $admin = Admin::where('email', 'admin@example.com')->first();
        $user = User::where('email', 'general@example.com')->first();

        $attendance = Attendance::create([
            'user_id' => $user->id,
            'date' => now()->toDateString(),
            'clock_in' => now()->setTime(9, 0),
            'clock_out' => now()->setTime(18, 0),
            'status' => \App\Enums\AttendanceStatus::AfterWork,
        ]);

        $this->actingAs($admin, 'admins');

        $response = $this->post(route('attendance_correction.store', $attendance->id), [
            'requested_clock_in' => '09:00',
            'requested_clock_out' => '18:00',
            'note' => '',
        ]);

        $response->assertSessionHasErrors(['note']);
    }
}
