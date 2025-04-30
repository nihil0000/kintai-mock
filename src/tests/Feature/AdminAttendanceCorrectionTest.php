<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Database\Seeders\DatabaseSeeder;
use App\Models\Admin;
use App\Models\User;
use App\Models\Attendance;
use App\Models\AttendanceCorrectionRequest;
use App\Enums\AttendanceCorrectionRequestStatus;

class AdminAttendanceCorrectionTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(DatabaseSeeder::class);
    }

    private function createRequest(User $user, string $status = 'pending'): AttendanceCorrectionRequest
    {
        $attendance = Attendance::create([
            'user_id' => $user->id,
            'date' => now()->toDateString(),
            'clock_in' => now()->setTime(9, 0),
            'clock_out' => now()->setTime(18, 0),
            'status' => \App\Enums\AttendanceStatus::AfterWork,
        ]);

        return AttendanceCorrectionRequest::create([
            'attendance_id' => $attendance->id,
            'user_id' => $user->id,
            'requested_clock_in' => now()->setTime(10, 0),
            'requested_clock_out' => now()->setTime(19, 0),
            'note' => '修正申請テスト',
            'status' => AttendanceCorrectionRequestStatus::from($status),
        ]);
    }

    /** @test */
    public function 承認待ちの修正申請が全て表示されている()
    {
        $admin = Admin::where('email', 'admin@example.com')->first();
        $user = User::factory()->create();
        $this->createRequest($user, 'pending');

        $this->actingAs($admin, 'admins');

        $response = $this->get(route('stamp_correction_request.index') . '?status=pending');

        $response->assertStatus(200);
        $response->assertSee('承認待ち');
        $response->assertSee('修正申請テスト');
    }

    /** @test */
    public function 承認済みの修正申請が全て表示されている()
    {
        $admin = Admin::where('email', 'admin@example.com')->first();
        $user = User::factory()->create();
        $this->createRequest($user, 'approved');

        $this->actingAs($admin, 'admins');

        $response = $this->get(route('stamp_correction_request.index') . '?status=approved');

        $response->assertStatus(200);
        $response->assertSee('承認済み');
        $response->assertSee('修正申請テスト');
    }

    /** @test */
    public function 修正申請の詳細内容が正しく表示されている()
    {
        $admin = Admin::where('email', 'admin@example.com')->first();
        $user = User::factory()->create();
        $request = $this->createRequest($user);

        $this->actingAs($admin, 'admins');

        $response = $this->get(route('admin.request.show', $request->id));

        $response->assertStatus(200);
        $response->assertSee('修正申請テスト');
        $response->assertSee('10:00');
        $response->assertSee('19:00');
    }

    /** @test */
    public function 修正申請の承認処理が正しく行われる()
    {
        $admin = Admin::where('email', 'admin@example.com')->first();
        $user = User::factory()->create();
        $request = $this->createRequest($user, 'pending');

        $this->actingAs($admin, 'admins');

        $response = $this->post(route('admin.request.update', $request->id));

        $response->assertRedirect();
        $this->assertDatabaseHas('attendance_correction_requests', [
            'id' => $request->id,
            'status' => AttendanceCorrectionRequestStatus::Approved->value,
        ]);
    }
}
