<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\User;

class LogoutTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function ログアウトができる()
    {
        // 事前にログイン状態を作る
        $user = User::factory()->create([
            'email_verified_at' => now(), // メール認証済み状態
        ]);

        $this->actingAs($user);

        // POSTでログアウトを実行（通常、ルートは POST /logout）
        $response = $this->post('/logout');

        // 認証されていない状態であることを確認
        $this->assertGuest();

        // リダイレクト先（ログイン画面など）を確認
        $response->assertRedirect('/login');
    }
}
