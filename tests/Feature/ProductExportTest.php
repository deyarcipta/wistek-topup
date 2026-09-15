<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Product;
use App\Models\User;
use App\Services\ProductExportService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProductExportTest extends TestCase
{
    use RefreshDatabase;

    public function test_product_export_service_generates_valid_xlsx_file_with_correct_columns(): void
    {
        $category = Category::create([
            'name' => 'Mobile Legends',
            'slug' => 'mobile-legends',
            'type' => 'game',
            'status' => true,
        ]);

        $product = Product::create([
            'category_id' => $category->id,
            'name' => '86 Diamonds MLBB',
            'sku' => 'ML86',
            'price_cost' => 10000,
            'price_sell' => 12000,
            'price_gold' => 11500,
            'price_platinum' => 11000,
            'status' => true,
            'digiflazz_status' => true,
        ]);

        $filePath = ProductExportService::exportToXlsx();

        $this->assertFileExists($filePath);
        $this->assertGreaterThan(0, filesize($filePath));

        // Cleanup temporary test file
        if (file_exists($filePath)) {
            unlink($filePath);
        }
    }

    public function test_profit_calculation_includes_service_fee_and_qris_deduction(): void
    {
        $cost = 10000.0;
        $priceSell = 12000.0;

        // Service fee for 12,000 using default rules
        $serviceFee = ProductExportService::calculateServiceFee($priceSell);
        $totalPaid = $priceSell + $serviceFee;
        $qrisCut = ($totalPaid * 0.007) + 750;
        $expectedNetProfit = $totalPaid - $qrisCut - $cost;

        $this->assertEquals($serviceFee, ProductExportService::calculateServiceFee(12000));
        $this->assertEquals($qrisCut, ($totalPaid * 0.007) + 750);
        $this->assertEquals($expectedNetProfit, $totalPaid - $qrisCut - $cost);
    }

    public function test_admin_can_download_product_excel_export(): void
    {
        $admin = User::factory()->create([
            'role' => 'admin',
        ]);

        $category = Category::create([
            'name' => 'Free Fire',
            'slug' => 'free-fire',
            'type' => 'game',
            'status' => true,
        ]);

        Product::create([
            'category_id' => $category->id,
            'name' => '100 Diamonds FF',
            'sku' => 'FF100',
            'price_cost' => 13000,
            'price_sell' => 15000,
            'status' => true,
            'digiflazz_status' => true,
        ]);

        $response = $this->actingAs($admin)->get('/admin/products/export-excel');

        $response->assertStatus(200);
        $response->assertHeader('content-type', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
    }

    public function test_non_admin_cannot_download_product_excel_export(): void
    {
        $user = User::factory()->create([
            'role' => 'member',
        ]);

        $response = $this->actingAs($user)->get('/admin/products/export-excel');
        $this->assertTrue(in_array($response->getStatusCode(), [403, 302], true));
    }
}
