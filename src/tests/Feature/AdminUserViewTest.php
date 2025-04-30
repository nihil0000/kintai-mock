<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Database\Seeders\DatabaseSeeder;
use Tests\TestCase;
use App\Models\Admin;
use App\Models\User;
use App\Models\Attendance;
use Carbon\Carbon;


class AdminUserViewTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(DatabaseSeeder::class);
    }

    /** @test */
    public function 管理者ユーザーが全一般ユーザーの「氏名」「メールアドレス」を確認できる()
    {
        $admin = Admin::where('email', 'admin@example.com')->first();

        User::create([
            'name' => '太郎',
            'email' => 'taro@example.com',
            'password' => 'password',
        ]);

        User::create([
            'name' => '花子',
            'email' => 'hanako@example.com',
            'password' => 'password',
        ]);

        $this->actingAs($admin, 'admins');

        $response = $this->get(route('admin.staff.index'));

        $response->assertStatus(200);
        $response->assertSee('太郎');
        $response->assertSee('taro@example.com');
        $response->assertSee('花子');
        $response->assertSee('hanako@example.com');
    }

    /** @test */
    public function ユーザーの勤怠情報が正しく表示される()
    {
        $admin = Admin::where('email', 'admin@example.com')->first();
        $user = User::create([
            'name' => '勤怠テストユーザー',
            'email' => 'test@example.com',
            'password' => 'password',
        ]);

        $attendance = Attendance::create([
            'user_id' => $user->id,
            'date' => now()->toDateString(),
            'clock_in' => now()->setTime(9, 0),
            'clock_out' => now()->setTime(18, 0),
            'status' => \App\Enums\AttendanceStatus::AfterWork,
        ]);

        $this->actingAs($admin, 'admins');

        $response = $this->get(route('attendance.show', $attendance->id));

        $response->assertStatus(200);
        $response->assertSee('勤怠テストユーザー');
        $response->assertSee('09:00');
        $response->assertSee('18:00');
    }

    /** @test */
    public function 「前月」を押下した時に表示月の前月の情報が表示される()
    {
        $admin = Admin::where('email', 'admin@example.com')->first();
        $user = User::factory()->create();

        $targetDate = now()->subMonth()->startOfMonth()->copy();
        Attendance::create([
            'user_id' => $user->id,
            'date' => $targetDate->copy()->day(5),
            'clock_in' => $targetDate->copy()->setTime(10, 0),
            'clock_out' => $targetDate->copy()->setTime(19, 0),
            'status' => \App\Enums\AttendanceStatus::AfterWork,
        ]);

        $this->actingAs($admin, 'admins');

        $response = $this->get(route('admin.attendance.show', [
            'user' => $user->id,
            'month' => $targetDate->format('Y-m')
        ]));

        $response->assertStatus(200);
        $response->assertSee($targetDate->format('Y/m'));
        $response->assertSee('10:00');
        $response->assertSee('19:00');
    }

    /** @test */
    public function 「翌月」を押下した時に表示月の前月の情報が表示される()
    {
        $admin = Admin::where('email', 'admin@example.com')->first();
        $user = User::factory()->create();

        $targetDate = now()->addMonth()->startOfMonth()->copy();
        Attendance::create([
            'user_id' => $user->id,
            'date' => $targetDate->copy()->day(10),
            'clock_in' => $targetDate->copy()->setTime(8, 0),
            'clock_out' => $targetDate->copy()->setTime(17, 0),
            'status' => \App\Enums\AttendanceStatus::AfterWork,
        ]);

        $this->actingAs($admin, 'admins');

        $response = $this->get(route('admin.attendance.show', [
            'user' => $user->id,
            'month' => $targetDate->format('Y-m')
        ]));

        $response->assertStatus(200);
        $response->assertSee($targetDate->format('Y/m'));
        $response->assertSee('08:00');
        $response->assertSee('17:00');
    }

    /** @test */
    public function 「詳細」を押下すると、その日の勤怠詳細画面に遷移する()
    {
        $admin = Admin::where('email', 'admin@example.com')->first();
        $user = User::factory()->create(['name' => '詳細ユーザー']);

        $attendance = Attendance::create([
            'user_id' => $user->id,
            'date' => now()->toDateString(),
            'clock_in' => now()->setTime(8, 0),
            'clock_out' => now()->setTime(17, 0),
            'status' => \App\Enums\AttendanceStatus::AfterWork,
        ]);

        $this->actingAs($admin, 'admins');

        $response = $this->get(route('attendance.show', $attendance->id));

        $response->assertStatus(200);
        $response->assertSee('詳細ユーザー');
        $response->assertSee('08:00');
        $response->assertSee('17:00');
    }
}
