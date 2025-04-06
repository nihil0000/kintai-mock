<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\User;

class LoginValidationTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function メールアドレスが未入力の場合バリデーションメッセージが表示される()
    {
        $response = $this->post('/login', [
            'email' => '',
            'password' => 'password123',
        ]);

        $response->assertSessionHasErrors(['email' => 'メールアドレスを入力してください']);
    }

    /** @test */
    public function パスワードが未入力の場合バリデーションメッセージが表示される()
    {
        $response = $this->post('/login', [
            'email' => 'test@example.com',
            'password' => '',
        ]);

        $response->assertSessionHasErrors(['password' => 'パスワードを入力してください']);
    }

    /** @test */
    public function 入力情報が間違っている場合バリデーションメッセージが表示される()
    {
        $response = $this->post('/login', [
            'email' => 'notexist@example.com',
            'password' => 'wrongpassword',
        ]);

        $response->assertSessionHasErrors([
            'email' => 'ログイン情報が登録されていません。'
        ]);
    }

    /** @test */
    public function 正しい情報が入力された場合ログイン処理が実行される()
    {
        // 事前にユーザーを作成
        $user = User::factory()->create([
            'email' => 'test@example.com',
            'password' => bcrypt('password123'),
            'email_verified_at' => now(), // ← メール認証済みであることが前提の場合
        ]);

        // ログイン実行
        $response = $this->post('/login', [
            'email' => 'test@example.com',
            'password' => 'password123',
        ]);

        // 認証されていることを確認
        $this->assertAuthenticatedAs($user);

        // 成功後のリダイレクト先（例：商品一覧）
        $response->assertRedirect(route('product.index'));
    }
}
