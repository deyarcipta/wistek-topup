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
    }
}
