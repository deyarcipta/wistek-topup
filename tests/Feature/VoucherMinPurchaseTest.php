<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Product;
use App\Models\Voucher;
use Database\Seeders\VoucherSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class VoucherMinPurchaseTest extends TestCase
{
    use RefreshDatabase;

    protected function createSampleProduct(float $price): Product
    {
        $category = Category::create([
            'name' => 'Sample Game',
            'slug' => 'sample-game-'.uniqid(),
            'type' => 'game',
            'status' => true,
        ]);

        return Product::create([
            'category_id' => $category->id,
            'name' => 'Diamond Test '.$price,
            'sku' => 'SKU-'.uniqid(),
            'price_cost' => $price * 0.9,
            'price_sell' => $price,
            'status' => true,
            'digiflazz_status' => true,
        ]);
    }

    public function test_validate_voucher_succeeds_when_min_purchase_is_met(): void
    {
        $product = $this->createSampleProduct(25000);

        $voucher = Voucher::create([
            'code' => 'TESTMIN20K',
            'type' => 'fixed',
            'value' => 2000,
            'min_purchase' => 20000,
            'is_active' => true,
        ]);

        $response = $this->postJson('/api/validate-voucher', [
            'code' => 'TESTMIN20K',
            'product_id' => $product->id,
        ]);

        $response->assertStatus(200);
        $response->assertJson([
            'success' => true,
            'code' => 'TESTMIN20K',
            'discount' => 2000,
            'formatted_discount' => 'Rp 2.000',
        ]);
    }

    public function test_validate_voucher_fails_when_product_price_below_min_purchase(): void
    {
        $product = $this->createSampleProduct(15000);

        $voucher = Voucher::create([
            'code' => 'TESTMIN50K',
            'type' => 'fixed',
            'value' => 5000,
            'min_purchase' => 50000,
            'is_active' => true,
        ]);

        $response = $this->postJson('/api/validate-voucher', [
            'code' => 'TESTMIN50K',
            'product_id' => $product->id,
        ]);

        $response->assertStatus(200);
        $response->assertJson([
            'success' => false,
        ]);
        $this->assertStringContainsString('50.000', $response->json('message'));
        $this->assertStringContainsString('15.000', $response->json('message'));
    }

    public function test_percentage_voucher_respects_max_discount_cap(): void
    {
        // 10% of 100,000 is 10,000, but max_discount is 5,000
        $product = $this->createSampleProduct(100000);

        $voucher = Voucher::create([
            'code' => 'DISC10MAX5K',
            'type' => 'percent',
            'value' => 10,
            'min_purchase' => 50000,
            'max_discount' => 5000,
            'is_active' => true,
        ]);

        $discount = $voucher->calculateDiscount($product->price_sell);
        $this->assertEquals(5000, $discount);

        // Under cap: 10% of 30,000 is 3,000
        $smallProduct = $this->createSampleProduct(30000);
        $voucher->update(['min_purchase' => 20000]);
        $discountSmall = $voucher->calculateDiscount($smallProduct->price_sell);
        $this->assertEquals(3000, $discountSmall);
    }

    public function test_seeded_vouchers_exist_and_function_properly(): void
    {
        $this->seed(VoucherSeeder::class);

        $v1 = Voucher::where('code', 'WISTEKGRAND1K')->first();
        $this->assertNotNull($v1);
        $this->assertEquals(1000, $v1->value);
        $this->assertEquals(10000, $v1->min_purchase);

        $v5 = Voucher::where('code', 'WISTEKMEMBER5K')->first();
        $this->assertNotNull($v5);
        $this->assertEquals(5000, $v5->value);
        $this->assertEquals(50000, $v5->min_purchase);

        // Under min purchase -> 0 discount
        $this->assertEquals(0, $v5->calculateDiscount(35000));

        // Meets min purchase -> 5000 discount
        $this->assertEquals(5000, $v5->calculateDiscount(55000));
    }
}
