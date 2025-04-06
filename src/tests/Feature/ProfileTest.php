<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\User;
use App\Models\Product;
use App\Models\Order;
use App\Models\Address;

class ProfileTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function プロフィールページにユーザー名とプロフィール画像が表示される()
    {
        $user = User::factory()->create(['name' => 'テスト太郎']);
        $this->actingAs($user);

        $response = $this->get(route('profile.show', ['page' => 'exhibit']));

        $response->assertStatus(200);
        $response->assertSee('テスト太郎');
        $response->assertSee('No Image'); // 画像未登録時の代替テキスト
    }

    /** @test */
    public function プロフィールページに出品した商品が表示される()
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        Product::create([
            'user_id' => $user->id,
            'product_name' => '出品商品A',
            'brand_name' => 'BrandA',
            'price' => 1000,
            'description' => '説明A',
            'status' => '良好',
            'image' => 'images/productA.jpg',
            'is_sold' => false,
        ]);

        $response = $this->get('/mypage?page=exhibit');

        $response->assertStatus(200);
        $response->assertSee('出品商品A');
    }

    /** @test */
    public function プロフィールページに購入した商品が表示される()
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $seller = User::factory()->create();

        $product = Product::create([
            'user_id' => $seller->id,
            'product_name' => '購入商品B',
            'brand_name' => 'BrandB',
            'price' => 2000,
            'description' => '説明B',
            'status' => 'やや傷あり',
            'image' => 'images/productB.jpg',
            'is_sold' => true,
        ]);

        Order::create([
            'user_id' => $user->id,
            'product_id' => $product->id,
            'payment_type' => 'カード支払い',
            'shipping_postal_code' => '100-0001',
            'shipping_address' => '東京都千代田区1-1',
            'shipping_building' => 'テストビル',
        ]);

        $response = $this->get('/mypage?page=order');

        $response->assertStatus(200);
        $response->assertSee('購入商品B');
    }

    /** @test */
    public function プロフィール編集画面に過去の設定値が初期表示される()
    {
        // ユーザー作成（プロフィール画像含む）
        $user = User::factory()->create([
            'name' => 'テスト太郎',
            'profile_image' => 'profile_images/test.jpg',
        ]);

        Address::create([
            'user_id' => $user->id,
            'postal_code' => '100-0001',
            'address' => '東京都千代田区1-1',
            'building' => 'テストビル',
        ]);

        // ログイン状態にする
        $this->actingAs($user);

        // プロフィール編集画面にアクセス
        $response = $this->get('/mypage/profile');

        // ステータス確認
        $response->assertStatus(200);

        // 各フィールドの初期表示値を確認
        $response->assertSee('テスト太郎');
        $response->assertSee('value="100-0001"', false); // 郵便番号
        $response->assertSee('東京都千代田区1-1');
        $response->assertSee('profile_images/test.jpg'); // 画像パスが表示されているか
    }
}
