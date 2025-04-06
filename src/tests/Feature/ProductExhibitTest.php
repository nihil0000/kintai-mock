<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\User;
use App\Models\Category;
use App\Models\Product;
use Database\Seeders\CategoriesTableSeeder;
use Illuminate\Http\UploadedFile;

class ProductExhibitTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function 商品出品画面から必要な情報を正しく保存できる()
    {
        // ユーザー作成＆ログイン
        $user = User::factory()->create();
        $this->actingAs($user);

        // カテゴリを2つ作成
        $this->seed(CategoriesTableSeeder::class);
        $categoryIds = Category::pluck('id')->take(2)->toArray();

        // 出品データ送信
        $response = $this->post('/exhibit', [
            'product_name' => '高性能イヤホン',
            'brand_name'   => 'SoundGood',
            'price'        => 12000,
            'description'  => 'ノイズキャンセリング搭載イヤホン',
            'status'       => '新品',
            'category' => $categoryIds,
            'image' => UploadedFile::fake()->create('dummy.jpg', 100) // 100KB の空ファイル
        ]);

        // リダイレクト確認（例：商品一覧へ）
        $response->assertRedirect(route('product.index'));

        // DBに保存されているか確認
        $this->assertDatabaseHas('products', [
            'product_name' => '高性能イヤホン',
            'brand_name'   => 'SoundGood',
            'price'        => 12000,
            'description'  => 'ノイズキャンセリング搭載イヤホン',
            'status'       => '新品',
            'user_id'      => $user->id,
        ]);

        // 商品とカテゴリの中間テーブル確認
        $product = Product::where('product_name', '高性能イヤホン')->first();
        foreach ($categoryIds as $categoryId) {
            $this->assertDatabaseHas('product_category', [
                'product_id'  => $product->id,
                'category_id' => $categoryId,
            ]);
        }
    }
}
