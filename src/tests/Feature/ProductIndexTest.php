<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\User;
use App\Models\Product;
use App\Models\Favorite;
use App\Models\Comment;
use App\Models\Category;
use Database\Seeders\ProductsTableSeeder;
use Database\Seeders\CategoriesTableSeeder;

class ProductIndexTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function 全商品が商品一覧に表示される()
    {
        // 先に10人のユーザーを作成してIDを確保
        User::factory()->count(10)->create();

        // その後Seederでproductsを登録
        $this->seed(ProductsTableSeeder::class);

        $response = $this->get(route('product.index'));

        $response->assertStatus(200);

        $response->assertSee('腕時計');
        $response->assertSee('HDD');
        $response->assertSee('玉ねぎ3束');
        $response->assertSee('革靴');
        $response->assertSee('ノートPC');
        $response->assertSee('マイク');
        $response->assertSee('ショルダーバッグ');
        $response->assertSee('タンブラー');
        $response->assertSee('コーヒーミル');
        $response->assertSee('メイクセット');
    }

    /** @test */
    public function 購入済みの商品にはSoldラベルが表示される()
    {
        // id: 1〜10 のユーザーを明示的に作成（外部キー制約を満たす）
        for ($i = 1; $i <= 10; $i++) {
            User::factory()->create(['id' => $i]);
        }

        // 商品データをSeederで投入
        $this->seed(ProductsTableSeeder::class);

        // 任意の商品（1件目）を取得
        $product = Product::first();

        // 購入済み状態にする（is_soldカラムがある前提）
        $product->is_sold = true;
        $product->save();

        // 商品一覧にアクセス
        $response = $this->get(route('product.index'));

        $response->assertStatus(200);

        // "Sold" の表示を確認（条件付きでBlade内に出力されているはず）
        $response->assertSee('Sold');
    }

    /** @test */
    public function 自分が出品した商品は一覧に表示されない()
    {
        // 自分（ログインユーザー）を id:1 で作成
        User::factory()->create(['id' => 1]);

        // 他人のユーザーもあらかじめ作成しておく（Seederで user_id: 2〜 を使っているため）
        for ($i = 2; $i <= 10; $i++) {
            \App\Models\User::factory()->create(['id' => $i]);
        }

        // 商品Seeder実行（user_id: 1〜10の商品が作られる）
        $this->seed(ProductsTableSeeder::class);

        // 自分としてログイン
        $this->actingAs(User::find(1));

        // 商品一覧ページへアクセス
        $response = $this->get(route('product.index'));

        $response->assertStatus(200);

        // 自分の商品（例：腕時計）が表示されていないこと
        $response->assertDontSee('腕時計');

        // 他人の商品（例：HDD）は表示されていること
        $response->assertSee('HDD');
    }

    /** @test */
    public function いいねした商品だけがマイリストに表示される()
    {
        // user_id: 1 のユーザーを作成
        User::factory()->create(['id' => 1]);

        // 他のユーザーも用意（Seeder対応）
        for ($i = 2; $i <= 10; $i++) {
            User::factory()->create(['id' => $i]);
        }

        // 商品Seeder実行（user_id: 1〜10の商品を作成）
        $this->seed(ProductsTableSeeder::class);

        // ログインユーザーとしてログイン
        $this->actingAs(User::find(1));

        // HDDの商品を取得し、いいねする
        $product = Product::where('product_name', 'HDD')->first();
        Favorite::create([
            'user_id' => 1,
            'product_id' => $product->id,
        ]);

        // マイリストページへアクセス（クエリパラメータで切り替え）
        $response = $this->get('/?page=mylist');

        $response->assertStatus(200);

        // いいねした商品は表示される
        $response->assertSee('HDD');

        // いいねしていない商品は表示されない（例：腕時計）
        $response->assertDontSee('腕時計');
    }

    /** @test */
    public function マイリストで購入済みの商品にSoldラベルが表示される()
    {
        // user_id: 1 のユーザーでログイン
        User::factory()->create(['id' => 1]);

        for ($i = 2; $i <= 10; $i++) {
            User::factory()->create(['id' => $i]);
        }

        // 商品Seeder実行（user_id: 1〜10）
        $this->seed(ProductsTableSeeder::class);

        // 対象の商品を取得（例：HDD）
        $product = Product::where('product_name', 'HDD')->first();

        // いいねを付ける
        Favorite::create([
            'user_id' => 1,
            'product_id' => $product->id,
        ]);

        // 商品を購入済みに変更
        $product->is_sold = true;
        $product->save();

        // ログイン
        $this->actingAs(\App\Models\User::find(1));

        // マイリストページにアクセス
        $response = $this->get('/?page=mylist');

        $response->assertStatus(200);

        // 「Sold」が表示されていることを確認
        $response->assertSee('Sold');
    }

    /** @test */
    public function マイリストで自分が出品した商品は表示されない()
    {
        // 自分のユーザー作成
        User::factory()->create(['id' => 1]);

        // 他のユーザーも Seeder 用に作成
        for ($i = 2; $i <= 10; $i++) {
            User::factory()->create(['id' => $i]);
        }

        // 商品をSeederから投入（user_id: 1〜10 の商品）
        $this->seed(ProductsTableSeeder::class);

        // ログイン
        $this->actingAs(User::find(1));

        // 自分が出品した商品（例：user_id:1 の商品）にいいねを付ける
        $ownProduct = Product::where('user_id', 1)->first();
        Favorite::create([
            'user_id' => 1,
            'product_id' => $ownProduct->id,
        ]);

        // 他人の商品（例：user_id:2）にもいいねを付ける（表示対象）
        $otherProduct = Product::where('user_id', 2)->first();
        Favorite::create([
            'user_id' => 1,
            'product_id' => $otherProduct->id,
        ]);

        // マイリストページにアクセス
        $response = $this->get('/?page=mylist');

        $response->assertStatus(200);

        // 他人の商品は表示される
        $response->assertSee($otherProduct->product_name);

        // 自分の商品は表示されない
        $response->assertDontSee($ownProduct->product_name);
    }

    /** @test */
    public function 未ログインユーザーはマイリストにアクセスできずログイン画面にリダイレクトされる()
    {
        // 未ログイン状態でマイリストにアクセス
        $response = $this->get('/?page=mylist');

        // ログイン画面にリダイレクトされる（302）
        $response->assertRedirect('/login');
    }

    /** @test */
    public function 商品名で部分一致検索ができる()
    {
        // ユーザーを作成してログイン（出品者除外条件のため）
        User::factory()->create(['id' => 1]);

        for ($i = 2; $i <= 10; $i++) {
            User::factory()->create(['id' => $i]);
        }

        // 商品をSeederから投入
        $this->seed(ProductsTableSeeder::class);

        // ログイン
        $this->actingAs(User::find(1));

        // 「時計」で検索（"腕時計" をヒットさせる）
        $response = $this->get('/?query=ねぎ');

        $response->assertStatus(200);

        // 部分一致する商品が表示される（例：玉ねぎ３束）
        $response->assertSee('ねぎ');

        // 一致しない商品は表示されない（例：HDD）
        $response->assertDontSee('HDD');
    }

    /** @test */
    public function 検索キーワードがマイリストでも保持されている()
    {
        // ユーザー作成とログイン
        User::factory()->create(['id' => 1]);
        for ($i = 2; $i <= 10; $i++) {
            User::factory()->create(['id' => $i]);
        }

        // 商品Seederを実行
        $this->seed(ProductsTableSeeder::class);

        // 「腕時計」商品はSeederで user_id:1 なので、検索対象にするには user_id を変更して再作成
        // もしくは別の商品（スマートウォッチ）を用意
        Product::create([
            'user_id' => 2, // ログインユーザーとは別
            'product_name' => 'スマートウォッチ',
            'brand_name' => 'FitWare',
            'price' => 10000,
            'description' => '高性能スマートウォッチ',
            'status' => '良好',
            'image' => 'images/watch.jpg',
            'is_sold' => false,
        ]);

        // ログイン
        $this->actingAs(User::find(1));

        // 「スマートウォッチ」に対していいねを追加（マイリストに表示されるように）
        $product = Product::where('product_name', 'スマートウォッチ')->first();

        Favorite::create([
            'user_id' => 1,
            'product_id' => $product->id,
        ]);

        // 検索キーワード「ウォッチ」でマイリストにアクセス
        $response = $this->get('/?page=mylist&query=ウォッチ');

        $response->assertStatus(200);

        // 検索フォームにキーワードが保持されていることを確認
        $response->assertSeeText('ウォッチ');

        // マイリストに該当商品が表示されていることを確認
        $response->assertSee('スマートウォッチ');
    }

    /** @test */
    public function 商品詳細ページに必要な情報がすべて表示されている()
    {
        // ユーザー作成
        $user = User::factory()->create(['name' => 'テストユーザー']);

        // 商品作成
        $product = Product::create([
            'user_id' => $user->id,
            'product_name' => 'スマートウォッチ',
            'brand_name' => 'FitWare',
            'price' => 10000,
            'description' => '高性能スマートウォッチ',
            'status' => '良好',
            'image' => 'images/watch.jpg',
            'is_sold' => false,
        ]);

        // カテゴリSeeder実行し、カテゴリ紐付け
        $this->seed(CategoriesTableSeeder::class);
        $categories = Category::inRandomOrder()->take(2)->get();
        $product->categories()->attach($categories->pluck('id'));

        // いいねを2件追加（SeederやFactoryではなく手動で）
        $user1 = User::factory()->create();
        $user2 = User::factory()->create();

        Favorite::create([
            'user_id' => $user1->id,
            'product_id' => $product->id,
        ]);

        Favorite::create([
            'user_id' => $user2->id,
            'product_id' => $product->id,
        ]);

        // コメント3件追加
        for ($i = 1; $i <= 3; $i++) {
            Comment::create([
                'user_id' => $user->id,
                'product_id' => $product->id,
                'comment' => "これはコメント{$i}です。",
            ]);
        }

        // 商品詳細ページにアクセス
        $response = $this->get(route('product.show', $product->id));
        $response->assertStatus(200);

        // 商品の基本情報を確認
        $response->assertSee('スマートウォッチ');
        $response->assertSee('FitWare');
        $response->assertSee('¥10,000');
        $response->assertSee('高性能スマートウォッチ');
        $response->assertSee('良好');

        // カテゴリ名表示
        foreach ($categories as $category) {
            $response->assertSee($category->category_name);
        }

        // いいね数とコメント数
        $response->assertSee('2');
        $response->assertSee('3');

        // コメント本文とユーザー名
        $response->assertSee('これはコメント1です。');
        $response->assertSee('テストユーザー');

        // 画像属性を確認
        $response->assertSee('商品画像');
    }

    /** @test */
    public function 複数カテゴリが表示される()
    {
        // ユーザー作成
        $user = User::factory()->create();

        // 商品作成
        $product = Product::create([
            'user_id' => $user->id,
            'product_name' => 'スマートウォッチ',
            'brand_name' => 'FitWare',
            'price' => 10000,
            'description' => '高性能スマートウォッチ',
            'status' => '良好',
            'image' => 'images/watch.jpg',
            'is_sold' => false,
        ]);

        // カテゴリSeeder実行し、2件を紐付け
        $this->seed(CategoriesTableSeeder::class);
        $categories = Category::inRandomOrder()->take(2)->get();
        $product->categories()->attach($categories->pluck('id'));

        // 商品詳細ページにアクセス
        $response = $this->get(route('product.show', $product->id));
        $response->assertStatus(200);

        // 複数カテゴリが表示されているか確認
        foreach ($categories as $category) {
            $response->assertSee($category->category_name);
        }
    }

    /** @test */
    public function いいねアイコンを押下すると商品が登録されて合計値が表示される()
    {
        // ユーザー作成とログイン
        $user = User::factory()->create();
        $this->actingAs($user);

        // 出品者とは別のユーザーで商品作成
        $owner = User::factory()->create();
        $product = Product::create([
            'user_id' => $owner->id,
            'product_name' => 'スマートウォッチ',
            'brand_name' => 'FitWare',
            'price' => 10000,
            'description' => '高性能スマートウォッチ',
            'status' => '良好',
            'image' => 'images/watch.jpg',
        ]);

        // いいね前の状態を確認（0件）
        $responseBefore = $this->get(route('product.show', $product->id));
        $responseBefore->assertStatus(200);
        $responseBefore->assertDontSee('<span>1</span>', false); // まだ0なので「1」が表示されない前提

        // いいね実行
        $this->post("/favorite/{$product->id}");

        // いいね後の状態を確認（1件）
        $responseAfter = $this->get(route('product.show', $product->id));
        $responseAfter->assertStatus(200);
        $responseAfter->assertSee('<span>1</span>', false); // 合計いいね数が1として表示されているか確認

        // DBに登録されていることも確認
        $this->assertDatabaseHas('favorites', [
            'user_id' => $user->id,
            'product_id' => $product->id,
        ]);
    }

    /** @test */
    public function いいねアイコンを押下するとアイコンが色付きで表示される()
    {
        // ユーザー作成とログイン
        $user = User::factory()->create();
        $this->actingAs($user);

        // 商品作成
        $product = Product::create([
            'user_id' => User::factory()->create()->id, // 別ユーザーの出品
            'product_name' => 'スマートウォッチ',
            'brand_name' => 'FitWare',
            'price' => 10000,
            'description' => '高性能スマートウォッチ',
            'status' => '良好',
            'image' => 'images/watch.jpg',
            'is_sold' => false,
        ]);

        // いいね処理を実行
        $this->post("/favorite/{$product->id}");

        // 商品詳細ページへアクセス
        $response = $this->get(route('product.show', $product->id));
        $response->assertStatus(200);

        // 色付き状態（例：fill-red-500クラス付きのSVG）が表示されていることを確認
        $response->assertSee('fill-yellow-400');
    }

    /** @test */
    public function いいねアイコンを再度押下するといいねが解除されて数が減少する()
    {
        // ユーザー作成 & ログイン
        $user = User::factory()->create();
        $this->actingAs($user);

        // 商品作成（他人の商品）
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

        // いいね実行
        $this->post("/favorite/{$product->id}");

        // 商品詳細ページで「1件のいいね」が表示されていることを確認
        $responseWithLike = $this->get(route('product.show', $product->id));
        $responseWithLike->assertStatus(200);
        $responseWithLike->assertSee('<span>1</span>', false); // いいねが1件表示

        // いいねを解除（再度同じエンドポイントにPOSTする or DELETEなどの仕様に応じて変更）
        $this->delete("/favorite/{$product->id}");

        // 再度アクセスして「0件」に戻っているか確認
        $responseWithoutLike = $this->get(route('product.show', $product->id));
        $responseWithoutLike->assertStatus(200);
        $responseWithoutLike->assertDontSee('<span>1</span>', false); // 1件ではなくなったことを確認
    }

    /** @test */
    public function ログイン済みのユーザーはコメントを送信できる()
    {
        // ユーザー作成 & ログイン
        $user = User::factory()->create();
        $this->actingAs($user);

        // 商品作成（他人の商品）
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

        // コメント送信（POST）
        $this->post("/comment/{$product->id}", [
            'comment' => 'とても良さそうですね！',
        ]);

        // コメントがDBに保存されていることを確認
        $this->assertDatabaseHas('comments', [
            'user_id' => $user->id,
            'product_id' => $product->id,
            'comment' => 'とても良さそうですね！',
        ]);

        // 商品詳細ページを確認
        $response = $this->get(route('product.show', $product->id));
        $response->assertStatus(200);

        // コメント本文が表示されていること
        $response->assertSee('とても良さそうですね！');

        // コメント数が「1」で表示されている（<span>1</span> を含む）
        $response->assertSee('<span>1</span>', false);
    }

    /** @test */
    public function 未ログインユーザーはコメントを送信できずログイン画面にリダイレクトされる()
    {
        // ユーザーと商品作成
        $user = User::factory()->create();
        $product = Product::create([
            'user_id' => $user->id,
            'product_name' => 'スマートウォッチ',
            'brand_name' => 'FitWare',
            'price' => 10000,
            'description' => '高性能スマートウォッチ',
            'status' => '良好',
            'image' => 'images/watch.jpg',
            'is_sold' => false,
        ]);

        // コメント送信（未ログイン状態）
        $response = $this->post("/comment/{$product->id}", [
            'comment' => '未ログインユーザーのコメント',
        ]);

        // ログイン画面にリダイレクトされることを確認
        $response->assertRedirect('/login');
    }

    /** @test */
    public function コメントが256文字以上の場合はバリデーションエラーが表示される()
    {
        // ユーザー作成＆ログイン
        $user = User::factory()->create();
        $this->actingAs($user);

        // 商品作成
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

        // 256文字のコメントを送信
        $response = $this->post("/comment/{$product->id}", [
            'comment' => str_repeat('あ', 256),
        ]);

        // セッションにバリデーションエラーがあることを確認
        $response->assertSessionHasErrors(['comment']);

        // リダイレクトされる（通常は元のページ）
        $response->assertStatus(302);
    }

    /** @test */
    public function コメントが未入力の場合はバリデーションエラーが表示される()
    {
        // ユーザー作成＆ログイン
        $user = User::factory()->create();
        $this->actingAs($user);

        // 商品作成
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

        // 空のコメントを送信
        $response = $this->post("/comment/{$product->id}", [
            'comment' => '',
        ]);

        // セッションにバリデーションエラーがあることを確認
        $response->assertSessionHasErrors(['comment']);

        // リダイレクトされることを確認（通常は元のページに戻る）
        $response->assertStatus(302);
    }
}
