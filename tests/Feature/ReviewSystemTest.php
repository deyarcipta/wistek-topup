<?php

namespace Tests\Feature;

use App\Filament\Resources\Categories\CategoryResource;
use App\Filament\Resources\Members\MemberResource;
use App\Filament\Resources\Products\ProductResource;
use App\Filament\Resources\Reviews\ReviewResource;
use App\Filament\Resources\Staff\StaffResource;
use App\Filament\Resources\SubCategories\SubCategoryResource;
use App\Filament\Resources\Transactions\TransactionResource;
use App\Filament\Resources\Vouchers\VoucherResource;
use App\Models\Category;
use App\Models\Product;
use App\Models\Review;
use App\Models\Setting;
use App\Models\Transaction;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ReviewSystemTest extends TestCase
{
    use RefreshDatabase;

    public function test_customer_can_submit_review_for_success_transaction(): void
    {
        $category = Category::create([
            'name' => 'Mobile Legends',
            'slug' => 'mobile-legends',
            'type' => 'game',
            'status' => true,
        ]);

        $product = Product::create([
            'category_id' => $category->id,
            'name' => '86 Diamonds',
            'sku' => 'MLBB86',
            'price_cost' => 19000,
            'price_sell' => 20500,
            'status' => true,
        ]);

        $transaction = Transaction::create([
            'invoice' => 'INV-TEST-001',
            'product_id' => $product->id,
            'sku' => $product->sku,
            'category_name' => $category->name,
            'product_name' => $product->name,
            'target_no' => '12345678 (1234)',
            'price' => 20500,
            'payment_method' => 'QRIS',
            'payment_status' => 'paid',
            'topup_status' => 'success',
            'customer_phone' => '081234567890',
        ]);

        $response = $this->post('/review/submit', [
            'invoice' => 'INV-TEST-001',
            'rating' => 5,
            'name' => 'Budi Gamer',
            'comment' => 'Mantap sekali langsung masuk dalam hitungan detik!',
        ]);

        $response->assertSessionHas('success');
        $this->assertDatabaseHas('reviews', [
            'transaction_id' => $transaction->id,
            'name' => 'Budi Gamer',
            'rating' => 5,
            'comment' => 'Mantap sekali langsung masuk dalam hitungan detik!',
            'is_visible' => true,
        ]);
    }

    public function test_customer_cannot_submit_review_for_unpaid_transaction(): void
    {
        $category = Category::create([
            'name' => 'Free Fire',
            'slug' => 'free-fire',
            'type' => 'game',
            'status' => true,
        ]);

        $product = Product::create([
            'category_id' => $category->id,
            'name' => '100 Diamonds',
            'sku' => 'FF100',
            'price_cost' => 14000,
            'price_sell' => 15000,
            'status' => true,
        ]);

        $transaction = Transaction::create([
            'invoice' => 'INV-UNPAID-001',
            'product_id' => $product->id,
            'sku' => $product->sku,
            'category_name' => $category->name,
            'product_name' => $product->name,
            'target_no' => '987654321',
            'price' => 15000,
            'payment_method' => 'QRIS',
            'payment_status' => 'unpaid',
            'topup_status' => 'pending',
            'customer_phone' => '081234567890',
        ]);

        $response = $this->post('/review/submit', [
            'invoice' => 'INV-UNPAID-001',
            'rating' => 5,
            'name' => 'User Unpaid',
            'comment' => 'Mencoba mengulas padahal belum bayar.',
        ]);

        $response->assertSessionHas('error');
        $this->assertDatabaseMissing('reviews', [
            'transaction_id' => $transaction->id,
        ]);
    }

    public function test_cannot_submit_duplicate_review_for_same_transaction(): void
    {
        $category = Category::create([
            'name' => 'PUBG Mobile',
            'slug' => 'pubg-mobile',
            'type' => 'game',
            'status' => true,
        ]);

        $product = Product::create([
            'category_id' => $category->id,
            'name' => '60 UC',
            'sku' => 'PUBG60',
            'price_cost' => 14000,
            'price_sell' => 15000,
            'status' => true,
        ]);

        $transaction = Transaction::create([
            'invoice' => 'INV-DUP-001',
            'product_id' => $product->id,
            'sku' => $product->sku,
            'category_name' => $category->name,
            'product_name' => $product->name,
            'target_no' => '555666777',
            'price' => 15000,
            'payment_method' => 'QRIS',
            'payment_status' => 'paid',
            'topup_status' => 'success',
            'customer_phone' => '081234567890',
        ]);

        // First submit
        $this->post('/review/submit', [
            'invoice' => 'INV-DUP-001',
            'rating' => 5,
            'name' => 'First Reviewer',
            'comment' => 'Ulasan pertama.',
        ]);

        // Second submit attempt
        $response = $this->post('/review/submit', [
            'invoice' => 'INV-DUP-001',
            'rating' => 4,
            'name' => 'Second Reviewer',
            'comment' => 'Ulasan kedua yang harusnya ditolak.',
        ]);

        $response->assertSessionHas('info');
        $this->assertEquals(1, Review::where('transaction_id', $transaction->id)->count());
    }

    public function test_homepage_respects_visibility_and_limit_settings(): void
    {
        Setting::set('review_section_enabled', '1');
        Setting::set('review_display_limit', '2');

        Review::create([
            'name' => 'Reviewer Satu',
            'role_or_title' => 'MLBB Player',
            'rating' => 5,
            'comment' => 'Komentar ulasan nomor satu.',
            'is_visible' => true,
            'sort_order' => 1,
        ]);

        Review::create([
            'name' => 'Reviewer Dua',
            'role_or_title' => 'FF Player',
            'rating' => 5,
            'comment' => 'Komentar ulasan nomor dua.',
            'is_visible' => true,
            'sort_order' => 2,
        ]);

        Review::create([
            'name' => 'Reviewer Tiga',
            'role_or_title' => 'PUBG Player',
            'rating' => 5,
            'comment' => 'Komentar ulasan nomor tiga.',
            'is_visible' => true,
            'sort_order' => 3,
        ]);

        Review::create([
            'name' => 'Reviewer Sembunyi',
            'role_or_title' => 'Hidden Player',
            'rating' => 1,
            'comment' => 'Ulasan disembunyikan oleh admin.',
            'is_visible' => false,
            'sort_order' => 0,
        ]);

        $response = $this->get('/');
        $response->assertStatus(200);

        // Visible within limit
        $response->assertSee('Reviewer Satu');
        $response->assertSee('Reviewer Dua');

        // Hidden review should NOT be seen
        $response->assertDontSee('Reviewer Sembunyi');

        // Beyond limit should NOT be seen
        $response->assertDontSee('Reviewer Tiga');
    }

    public function test_homepage_hides_reviews_when_setting_disabled(): void
    {
        Setting::set('review_section_enabled', '0');

        Review::create([
            'name' => 'Reviewer Unseen',
            'role_or_title' => 'Player',
            'rating' => 5,
            'comment' => 'Tidak boleh tampil.',
            'is_visible' => true,
            'sort_order' => 1,
        ]);

        $response = $this->get('/');
        $response->assertStatus(200);
        $response->assertDontSee('Ulasan Pelanggan Setia');
        $response->assertDontSee('Reviewer Unseen');
    }

    public function test_navigation_groups_are_properly_configured(): void
    {
        $this->assertEquals('Katalog Produk', CategoryResource::getNavigationGroup());
        $this->assertEquals('Katalog Produk', SubCategoryResource::getNavigationGroup());
        $this->assertEquals('Katalog Produk', ProductResource::getNavigationGroup());

        $this->assertEquals('Pengguna & Member', MemberResource::getNavigationGroup());
        $this->assertEquals('Pengguna & Member', StaffResource::getNavigationGroup());

        $this->assertEquals('Promosi & Konten', VoucherResource::getNavigationGroup());
        $this->assertEquals('Promosi & Konten', ReviewResource::getNavigationGroup());

        $this->assertNull(TransactionResource::getNavigationGroup());
    }
}
