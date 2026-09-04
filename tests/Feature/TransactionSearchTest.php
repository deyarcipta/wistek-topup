<?php

namespace Tests\Feature;

use App\Models\Transaction;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TransactionSearchTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test history search page loads successfully.
     */
    public function test_history_search_page_loads_successfully()
    {
        $response = $this->get('/history');

        $response->assertStatus(200)
            ->assertSee('Lacak Pembayaran')
            ->assertSee('Periksa Transaksi');
    }

    /**
     * Test searching for a non-existent invoice shows clean error notification without crash.
     */
    public function test_searching_non_existent_invoice_returns_error_message()
    {
        $response = $this->from('/history')->post('/history', [
            'invoice' => 'INV-INVALID-999999',
        ]);

        $response->assertRedirect('/history');
        $response->assertSessionHas('error', 'Kode invoice tidak ditemukan! Silakan periksa kembali.');

        $followUp = $this->get('/history');
        $followUp->assertStatus(200)
            ->assertSee('Kode invoice tidak ditemukan! Silakan periksa kembali.');
    }

    /**
     * Test searching for a valid invoice redirects to transaction detail page.
     */
    public function test_searching_valid_invoice_redirects_to_transaction_page()
    {
        $transaction = Transaction::create([
            'invoice' => 'INV-20260904-TEST01',
            'reference' => 'REF-12345',
            'category_name' => 'Mobile Legends',
            'product_name' => '86 Diamonds',
            'sku' => 'ML86',
            'target_no' => '12345678 (1234)',
            'customer_phone' => '081234567890',
            'price' => 20000,
            'payment_method' => 'QRIS',
            'payment_status' => 'unpaid',
            'topup_status' => 'pending',
        ]);

        $response = $this->post('/history', [
            'invoice' => 'INV-20260904-TEST01',
        ]);

        $response->assertRedirect('/transaction/'.$transaction->invoice);

        $detailResponse = $this->get('/transaction/'.$transaction->invoice);
        $detailResponse->assertStatus(200)
            ->assertSee('INV-20260904-TEST01')
            ->assertSee('Mobile Legends');
    }

    /**
     * Test direct access to non-existent transaction URL redirects gracefully to history with error.
     */
    public function test_accessing_invalid_transaction_url_redirects_to_history()
    {
        $response = $this->get('/transaction/INV-NONEXISTENT-999');

        $response->assertRedirect('/history');
        $response->assertSessionHas('error', 'Kode invoice tidak ditemukan! Silakan periksa kembali.');
    }
}
