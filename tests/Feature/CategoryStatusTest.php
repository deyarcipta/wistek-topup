<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Product;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CategoryStatusTest extends TestCase
{
    use RefreshDatabase;

    public function test_active_categories_appear_on_homepage(): void
    {
        $activeCat = Category::create([
            'name' => 'Active Game',
            'slug' => 'active-game',
            'type' => 'game',
            'status' => true,
        ]);

        $inactiveCat = Category::create([
            'name' => 'Inactive Game',
            'slug' => 'inactive-game',
            'type' => 'game',
            'status' => false,
        ]);

        $response = $this->get('/');

        $response->assertStatus(200);
        $response->assertSee('Active Game');
        $response->assertDontSee('Inactive Game');
    }

    public function test_inactive_category_cannot_be_viewed_directly(): void
    {
        $inactiveCat = Category::create([
            'name' => 'Hidden Game',
            'slug' => 'hidden-game',
            'type' => 'game',
            'status' => false,
        ]);

        $response = $this->get('/topup/hidden-game');

        $response->assertStatus(404);
    }

    public function test_checkout_is_blocked_for_inactive_category(): void
    {
        $inactiveCat = Category::create([
            'name' => 'Offline Game',
            'slug' => 'offline-game',
            'type' => 'game',
            'status' => false,
        ]);

        $product = Product::create([
            'category_id' => $inactiveCat->id,
            'name' => '100 Diamonds',
            'sku' => 'OFF-100',
            'price_cost' => 10000,
            'price_sell' => 12000,
            'status' => true,
            'digiflazz_status' => true,
        ]);

        $response = $this->post('/checkout', [
            'category_id' => $inactiveCat->id,
            'product_id' => $product->id,
            'payment_method' => 'QRIS',
            'target_id' => '123456',
            'customer_phone' => '08123456789',
        ]);

        $response->assertSessionHasErrors(['error' => 'Kategori / game ini sedang dinonaktifkan sementara.']);
    }
}
