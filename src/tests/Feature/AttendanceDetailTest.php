<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Database\Seeders\DatabaseSeeder;
use Tests\TestCase;
use App\Models\User;
use App\Models\Attendance;
use App\Models\BreakTime;
use Carbon\Carbon;

class AttendanceDetailTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(DatabaseSeeder::class);
    }

    /** @test */
    public function 勤怠詳細画面の「名前」がログインユーザーの氏名になっている()
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
    }

    /** @test */
    public function 勤怠詳細画面の「日付」が選択した日付になっている()
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
        $response->assertSee(now()->format('n月j日'));
    }

    /** @test */
    public function 出勤退勤時間がログインユーザーの打刻と一致している()
    {
        $user = User::where('email', 'general@example.com')->first();
        $attendance = Attendance::create([
            'user_id' => $user->id,
            'date' => now()->toDateString(),
            'clock_in' => Carbon::today()->setTime(9, 0),
            'clock_out' => Carbon::today()->setTime(18, 0),
            'status' => \App\Enums\AttendanceStatus::AfterWork,
        ]);

        $this->actingAs($user);

        $response = $this->get(route('attendance.show', $attendance->id));

        $response->assertStatus(200);
        $response->assertSee('09:00');
        $response->assertSee('18:00');
    }

    /** @test */
    public function 休憩時間がログインユーザーの打刻と一致している()
    {
        $user = User::where('email', 'general@example.com')->first();
        $attendance = Attendance::create([
            'user_id' => $user->id,
            'date' => now()->toDateString(),
            'clock_in' => Carbon::today()->setTime(9, 0),
            'clock_out' => Carbon::today()->setTime(18, 0),
            'status' => \App\Enums\AttendanceStatus::AfterWork,
        ]);

        BreakTime::create([
            'attendance_id' => $attendance->id,
            'break_start' => Carbon::today()->setTime(12, 0),
            'break_end' => Carbon::today()->setTime(13, 0),
        ]);

        $this->actingAs($user);

        $response = $this->get(route('attendance.show', $attendance->id));

        $response->assertStatus(200);
        $response->assertSee('12:00');
        $response->assertSee('13:00');
    }

    /** @test */
    public function 出勤時間が退勤時間より後になっている場合、エラーメッセージが表示される()
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
        $user = User::where('email', 'general@example.com')->first();
        $attendance = Attendance::create([
            'user_id' => $user->id,
            'date' => now()->toDateString(),
            'clock_in' => now()->setTime(9, 0),
            'clock_out' => now()->setTime(18, 0),
            'status' => \App\Enums\AttendanceStatus::AfterWork,
        ]);

        $this->actingAs($user);

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
        $user = User::where('email', 'general@example.com')->first();
        $attendance = Attendance::create([
            'user_id' => $user->id,
            'date' => now()->toDateString(),
            'clock_in' => now()->setTime(9, 0),
            'clock_out' => now()->setTime(18, 0),
            'status' => \App\Enums\AttendanceStatus::AfterWork,
        ]);

        $this->actingAs($user);

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
        $user = User::where('email', 'general@example.com')->first();
        $attendance = Attendance::create([
            'user_id' => $user->id,
            'date' => now()->toDateString(),
            'clock_in' => now()->setTime(9, 0),
            'clock_out' => now()->setTime(18, 0),
            'status' => \App\Enums\AttendanceStatus::AfterWork,
        ]);

        $this->actingAs($user);

        $response = $this->post(route('attendance_correction.store', $attendance->id), [
            'requested_clock_in' => '09:00',
            'requested_clock_out' => '18:00',
            'note' => '',
        ]);

        $response->assertSessionHasErrors(['note']);
    }

    /** @test */
    public function 修正申請処理が実行される()
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

        $response = $this->post(route('attendance_correction.store', $attendance->id), [
            'requested_clock_in' => '10:00',
            'requested_clock_out' => '19:00',
            'note' => '修正申請テスト',
        ]);

        $response->assertRedirect(route('attendance.show', $attendance->id));

        $this->assertDatabaseHas('attendance_correction_requests', [
            'attendance_id' => $attendance->id,
            'requested_clock_in' => now()->format('Y-m-d') . ' 10:00:00',
            'requested_clock_out' => now()->format('Y-m-d') . ' 19:00:00',
            'note' => '修正申請テスト',
        ]);
    }

    /** @test */
    public function 「承認待ち」にログインユーザーが行った申請が全て表示されていること()
    {
        $user = User::where('email', 'general@example.com')->first();
        $this->actingAs($user);

        $attendance1 = Attendance::create([
            'user_id' => $user->id,
            'date' => now()->toDateString(),
            'clock_in' => now()->setTime(9, 0),
            'clock_out' => now()->setTime(18, 0),
            'status' => \App\Enums\AttendanceStatus::AfterWork,
        ]);

        $attendance2 = Attendance::create([
            'user_id' => $user->id,
            'date' => now()->addDay()->toDateString(),
            'clock_in' => now()->addDay()->setTime(9, 0),
            'clock_out' => now()->addDay()->setTime(18, 0),
            'status' => \App\Enums\AttendanceStatus::AfterWork,
        ]);

        $this->post(route('attendance_correction.store', $attendance1->id), [
            'requested_clock_in' => '10:00',
            'requested_clock_out' => '19:00',
            'note' => '修正申請テスト1',
        ]);

        $this->post(route('attendance_correction.store', $attendance2->id), [
            'requested_clock_in' => '11:00',
            'requested_clock_out' => '20:00',
            'note' => '修正申請テスト2',
        ]);

        $response = $this->get(route('stamp_correction_request.index'));

        $response->assertStatus(200);

        $response->assertSee('修正申請テスト1');
        $response->assertSee('修正申請テスト2');
    }

    /** @test */
    public function 「承認済み」に管理者が承認した修正申請が全て表示されている()
    {
        $user = User::where('email', 'general@example.com')->first();
        $admin = \App\Models\Admin::where('email', 'admin@example.com')->first();

        $this->actingAs($user);

        $attendance1 = Attendance::create([
            'user_id' => $user->id,
            'date' => now()->toDateString(),
            'clock_in' => now()->setTime(9, 0),
            'clock_out' => now()->setTime(18, 0),
            'status' => \App\Enums\AttendanceStatus::AfterWork,
        ]);

        $attendance2 = Attendance::create([
            'user_id' => $user->id,
            'date' => now()->addDay()->toDateString(),
            'clock_in' => now()->addDay()->setTime(9, 0),
            'clock_out' => now()->addDay()->setTime(18, 0),
            'status' => \App\Enums\AttendanceStatus::AfterWork,
        ]);

        $this->post(route('attendance_correction.store', $attendance1->id), [
            'requested_clock_in' => '10:00',
            'requested_clock_out' => '19:00',
            'note' => '修正申請テスト1',
        ]);

        $this->post(route('attendance_correction.store', $attendance2->id), [
            'requested_clock_in' => '11:00',
            'requested_clock_out' => '20:00',
            'note' => '修正申請テスト2',
        ]);

        // 管理者で承認
        $this->actingAs($admin, 'admins');

        $requests = \App\Models\AttendanceCorrectionRequest::whereIn('attendance_id', [$attendance1->id, $attendance2->id])->get();

        foreach ($requests as $request) {
            $this->post(route('admin.request.update', $request->id));
        }

        $this->actingAs($user);

        $response = $this->get(route('stamp_correction_request.index') . '?status=approved');

        $response->assertStatus(200);

        $response->assertSee('承認済み');
        $response->assertSee('修正申請テスト1');
        $response->assertSee('修正申請テスト2');
    }

    /** @test */
    public function 各申請の「詳細」を押下すると申請詳細画面に遷移する()
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

        $this->post(route('attendance_correction.store', $attendance->id), [
            'requested_clock_in' => '10:00',
            'requested_clock_out' => '19:00',
            'note' => '修正申請テスト',
        ]);

        $request = \App\Models\AttendanceCorrectionRequest::where('attendance_id', $attendance->id)->first();

        $response = $this->get(route('stamp_correction_request.index', $request->id));

        $response->assertStatus(200);
        $response->assertSee('修正申請テスト');
    }
}
