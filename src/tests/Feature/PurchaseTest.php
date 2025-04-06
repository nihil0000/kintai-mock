<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\User;
use App\Models\Product;

class PurchaseTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function 購入するボタンを押下すると購入が完了して商品一覧にリダイレクトされる()
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $product = Product::create([
            'user_id' => User::factory()->create()->id,
            'product_name' => 'スマートウォッチ',
            'brand_name' => 'FitWare',
            'price' => 10000,
            'description' => '高性能スマートウォッチ',
            'status' => '良好',
            'image' => 'images/watch.jpg',
            'is_sold' => false,
        ]);

        $response = $this->post("/purchase/{$product->id}", [
            'payment' => 'コンビニ支払い',
            'postal_code' => '123-4567',
            'address' => '東京都渋谷区1-2-3',
            'building' => 'フリマビル 202',
        ]);

        // 処理成功後、商品一覧にリダイレクトされることを確認
        $response->assertRedirect(route('product.index'));

        // 購入済みとして更新されていることを確認
        $this->assertDatabaseHas('products', [
            'id' => $product->id,
            'is_sold' => true,
        ]);
    }

    /** @test */
    public function 購入した商品は商品一覧でsoldと表示される()
    {
        // ユーザー作成＆ログイン
        $user = User::factory()->create();
        $this->actingAs($user);

        // 商品作成（他人の商品）
        $seller = User::factory()->create();
        $product = Product::create([
            'user_id' => $seller->id,
            'product_name' => 'スマートウォッチ',
            'brand_name' => 'FitWare',
            'price' => 10000,
            'description' => '高性能スマートウォッチ',
            'status' => '良好',
            'image' => 'images/watch.jpg',
            'is_sold' => false,
        ]);

        // 購入処理（支払い情報と住所を一緒に送信）
        $response = $this->post(route('purchase.store', ['product' => $product->id]), [
            'payment' => 'コンビニ支払い',
            'postal_code' => '100-0001',
            'address' => '東京都千代田区千代田1-1',
            'building' => '皇居',
        ]);

        // 購入完了後に商品一覧にリダイレクトされていること
        $response->assertRedirect(route('product.index'));

        // 商品一覧を取得し、「Sold」が表示されているか確認
        $indexResponse = $this->get('/');
        $indexResponse->assertStatus(200);
        $indexResponse->assertSee('Sold');
    }

    /** @test */
    public function 購入した商品がプロフィールの購入一覧に表示される()
    {
        // ユーザー作成＆ログイン
        $user = User::factory()->create();
        $this->actingAs($user);

        // 商品作成（他人の商品）
        $seller = User::factory()->create();
        $product = Product::create([
            'user_id' => $seller->id,
            'product_name' => 'スマートウォッチ',
            'brand_name' => 'FitWare',
            'price' => 10000,
            'description' => '高性能スマートウォッチ',
            'status' => '良好',
            'image' => 'images/watch.jpg',
            'is_sold' => false,
        ]);

        // 購入処理（支払い・住所を含む）
        $this->post(route('purchase.store', ['product' => $product->id]), [
            'payment' => 'コンビニ支払い',
            'postal_code' => '100-0001',
            'address' => '東京都千代田区千代田1-1',
            'building' => '皇居',
        ]);

        // プロフィールページへアクセス
        $response = $this->get('/mypage?page=order');

        $response->assertStatus(200);

        // 商品名が表示されていること（購入一覧に含まれている）
        $response->assertSee('スマートウォッチ');
    }

    /** @test */
    public function 支払い方法を選択すると小計画面に即時反映される()
    {
        // ユーザー作成＆ログイン
        $user = User::factory()->create();
        $this->actingAs($user);

        // 商品作成
        $seller = User::factory()->create();
        $product = Product::create([
            'user_id' => $seller->id,
            'product_name' => 'スマートウォッチ',
            'brand_name' => 'FitWare',
            'price' => 10000,
            'description' => '高性能スマートウォッチ',
            'status' => '良好',
            'image' => 'images/watch.jpg',
            'is_sold' => false,
        ]);

        // プルダウンで「カード支払い」を選択してアクセス
        $response = $this->get(route('purchase.show', [
            'product' => $product->id,
            'payment' => 'カード支払い',
        ]));

        $response->assertStatus(200);

        // 選択した支払い方法が表示されているか確認
        $response->assertSee('カード支払い');
    }

    /** @test */
    public function 登録した住所が商品購入画面に正しく反映される()
    {
        // ユーザー作成 & ログイン
        $user = User::factory()->create();
        $this->actingAs($user);

        // 商品作成（他人の商品）
        $seller = User::factory()->create();
        $product = Product::create([
            'user_id' => $seller->id,
            'product_name' => 'スマートウォッチ',
            'brand_name' => 'FitWare',
            'price' => 10000,
            'description' => '高性能スマートウォッチ',
            'status' => '良好',
            'image' => 'images/watch.jpg',
            'is_sold' => false,
        ]);

        // 「変更する」画面で送付先情報を登録（セッションに保存）
        $this->withSession([
            'purchase_address_' . $product->id => [
                'postal_code' => '123-4567',
                'address' => '東京都新宿区テスト町1-2-3',
                'building' => 'テストビル101'
            ]
        ]);

        // 購入画面にアクセス
        $response = $this->get(route('purchase.show', [
            'product' => $product->id,
        ]));

        $response->assertStatus(200);

        // セッションから反映された住所が表示されているか確認
        $response->assertSee('〒 123-4567');
        $response->assertSee('東京都新宿区テスト町1-2-3テストビル101');
    }

    /** @test */
    public function 商品購入時に送付先住所が正しく登録される()
    {
        // ユーザー作成 & ログイン
        $user = User::factory()->create();
        $this->actingAs($user);

        // 出品者・商品作成
        $seller = User::factory()->create();
        $product = Product::create([
            'user_id' => $seller->id,
            'product_name' => 'スマートウォッチ',
            'brand_name' => 'FitWare',
            'price' => 10000,
            'description' => '高性能スマートウォッチ',
            'status' => '良好',
            'image' => 'images/watch.jpg',
            'is_sold' => false,
        ]);

        // セッションに住所を保存（画面遷移後に反映される前提）
        $this->withSession([
            'purchase_address_' . $product->id => [
                'postal_code' => '123-4567',
                'address'     => '東京都新宿区テスト町1-2-3',
                'building'    => 'テストビル101',
            ]
        ]);

        // 購入処理実行（支払い方法も指定）
        $response = $this->post(route('purchase.store', ['product' => $product->id]), [
            'payment'      => 'カード支払い',
            'postal_code'  => '123-4567',
            'address'      => '東京都新宿区テスト町1-2-3',
            'building'     => 'テストビル101',
        ]);

        $response->assertRedirect(route('product.index'));

        // ordersテーブルに購入データが保存されていることを確認
        $this->assertDatabaseHas('orders', [
            'user_id'              => $user->id,
            'product_id'           => $product->id,
            'payment_type'         => 'カード支払い',
            'shipping_postal_code' => '123-4567',
            'shipping_address'     => '東京都新宿区テスト町1-2-3',
            'shipping_building'    => 'テストビル101',
        ]);
    }
}
