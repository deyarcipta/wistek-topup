<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Product;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class DigiflazzSyncAndValidationTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test checking MLBB nickname endpoint returns correct result when successful.
     */
    public function test_check_mlbb_nickname_success()
    {
        // Mock the external API response
        Http::fake([
            'https://api.isan.eu.org/nickname/ml*' => Http::response([
                'success' => true,
                'game' => 'Mobile Legends',
                'id' => '12345678',
                'server' => '1234',
                'name' => 'TestPlayer',
            ], 200),
        ]);

        $response = $this->getJson('/api/check-mlbb?id=12345678&zone=1234');

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'nickname' => 'TestPlayer',
            ]);
    }

    /**
     * Test checking Free Fire nickname via multi-game check-nickname endpoint.
     */
    public function test_check_nickname_free_fire_success()
    {
        Http::fake([
            'https://api.isan.eu.org/nickname/ff*' => Http::response([
                'success' => true,
                'game' => 'Garena Free Fire',
                'id' => '10000000',
                'name' => 'FFBooyahHero',
            ], 200),
        ]);

        $response = $this->getJson('/api/check-nickname?game=free-fire&id=10000000');

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'game' => 'free-fire',
                'nickname' => 'FFBooyahHero',
            ]);
    }

    /**
     * Test checking MLBB nickname endpoint returns error when not found.
     */
    public function test_check_mlbb_nickname_not_found()
    {
        Http::fake([
            'https://api.isan.eu.org/nickname/ml*' => Http::response([
                'success' => false,
                'message' => 'Player ID not found.',
            ], 200),
        ]);

        $response = $this->getJson('/api/check-mlbb?id=99999999&zone=9999');

        $response->assertStatus(404)
            ->assertJson([
                'success' => false,
                'message' => 'Player ID not found.',
            ]);
    }

    /**
     * Test checking MLBB nickname fails when parameters are missing.
     */
    public function test_check_mlbb_nickname_missing_params()
    {
        $response = $this->getJson('/api/check-mlbb');

        $response->assertStatus(400)
            ->assertJson([
                'success' => false,
                'message' => 'User ID dan Zone ID wajib diisi.',
            ]);
    }

    /**
     * Test products:sync-digiflazz command updates price and status correctly.
     */
    public function test_sync_digiflazz_products_command()
    {
        // Create category and product in local DB
        $category = Category::create([
            'name' => 'Mobile Legends',
            'slug' => 'mobile-legends',
            'type' => 'game',
        ]);

        $product = Product::create([
            'category_id' => $category->id,
            'name' => '5 Diamonds',
            'sku' => 'MLBB_ID_5',
            'price_cost' => 1000.00,
            'price_sell' => 1500.00,
            'status' => 1,
        ]);

        // Mock Digiflazz pricelist call
        Http::fake([
            'https://api.digiflazz.com/v1/price-list' => Http::response([
                'data' => [
                    [
                        'buyer_sku_code' => 'MLBB_ID_5',
                        'price' => 1200.00,
                        'buyer_product_status' => true,
                        'seller_product_status' => true,
                    ],
                ],
            ], 200),
        ]);

        $this->artisan('products:sync-digiflazz')
            ->assertExitCode(0);

        // Verify product was updated
        $product->refresh();
        $this->assertEquals(1200.00, $product->price_cost);
        $this->assertEquals(1, $product->status);
        $this->assertTrue((bool) $product->digiflazz_status);
    }

    /**
     * Test products:sync-digiflazz automatically imports new active products and matches category.
     */
    public function test_sync_digiflazz_auto_imports_new_active_products()
    {
        // Category exists
        $category = Category::create([
            'name' => 'Mobile Legends',
            'slug' => 'mobile-legends',
            'type' => 'game',
        ]);

        // Mock Digiflazz response with a brand new SKU
        Http::fake([
            'https://api.digiflazz.com/v1/price-list' => Http::response([
                'data' => [
                    [
                        'buyer_sku_code' => 'ml10',
                        'product_name' => 'MOBILELEGEND - 10 Diamond',
                        'brand' => 'MOBILE LEGENDS',
                        'category' => 'Games',
                        'price' => 2965.00,
                        'buyer_product_status' => true,
                        'seller_product_status' => true,
                    ],
                ],
            ], 200),
        ]);

        $this->artisan('products:sync-digiflazz --force')
            ->assertExitCode(0);

        $this->assertDatabaseHas('products', [
            'sku' => 'ml10',
            'name' => 'MOBILELEGEND - 10 Diamond',
            'category_id' => $category->id,
            'price_cost' => 2965.00,
            'status' => 1,
            'digiflazz_status' => 1,
        ]);
    }

    /**
     * Test products:sync-digiflazz preserves admin-toggled store status (not forcing it to 1).
     */
    public function test_sync_digiflazz_preserves_store_status_toggled_by_admin()
    {
        $category = Category::create([
            'name' => 'Mobile Legends',
            'slug' => 'mobile-legends',
            'type' => 'game',
        ]);

        // Admin intentionally turned off this product in store
        $product = Product::create([
            'category_id' => $category->id,
            'name' => '28 Diamonds',
            'sku' => 'ML28',
            'price_cost' => 8000.00,
            'price_sell' => 9500.00,
            'status' => 0, // Admin disabled
            'digiflazz_status' => 1,
        ]);

        Http::fake([
            'https://api.digiflazz.com/v1/price-list' => Http::response([
                'data' => [
                    [
                        'buyer_sku_code' => 'ML28',
                        'product_name' => 'MOBILELEGEND - 28 Diamond',
                        'brand' => 'MOBILE LEGENDS',
                        'price' => 8675.00,
                        'buyer_product_status' => true,
                        'seller_product_status' => true,
                    ],
                ],
            ], 200),
        ]);

        $this->artisan('products:sync-digiflazz --force')
            ->assertExitCode(0);

        $product->refresh();
        $this->assertEquals(8675.00, $product->price_cost);
        $this->assertTrue((bool) $product->digiflazz_status);
        // Admin setting must NOT be overridden:
        $this->assertEquals(0, $product->status);
    }

    /**
     * Test sync gracefully handles rate-limit RC 83 without deactivating local products.
     */
    public function test_sync_digiflazz_handles_rate_limit_safely()
    {
        $category = Category::create([
            'name' => 'Mobile Legends',
            'slug' => 'mobile-legends',
            'type' => 'game',
        ]);

        $product = Product::create([
            'category_id' => $category->id,
            'name' => '5 Diamonds',
            'sku' => 'ml5',
            'price_cost' => 1480.00,
            'price_sell' => 2000.00,
            'status' => 1,
            'digiflazz_status' => 1,
        ]);

        // Mock Digiflazz rate limit RC 83
        Http::fake([
            'https://api.digiflazz.com/v1/price-list' => Http::response([
                'data' => [
                    'rc' => '83',
                    'message' => 'Anda telah mencapai limitasi pengecekan pricelist, silahkan coba beberapa saat lagi',
                ],
            ], 200),
        ]);

        $this->artisan('products:sync-digiflazz --force')
            ->assertExitCode(1);

        // Verify product was NOT deactivated
        $product->refresh();
        $this->assertEquals(1, $product->status);
        $this->assertTrue((bool) $product->digiflazz_status);
    }
}
