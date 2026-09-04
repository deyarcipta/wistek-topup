<?php

namespace Tests\Feature;

use App\Models\Transaction;
use App\Services\SimupIntegrationService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class SimupIntegrationTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test single transaction sync sends correct payload and updates transaction status.
     */
    public function test_single_transaction_syncs_successfully_to_simup()
    {
        Http::fake([
            '*/api/v1/webhook/topup-income' => Http::response([
                'success' => true,
                'message' => 'Income transaction logged successfully to SIMUP',
                'transaksi_id' => 101,
            ], 200),
        ]);

        $transaction = Transaction::create([
            'invoice' => 'INV-20260905-SIMUP01',
            'reference' => 'REF-SIMUP01',
            'category_name' => 'Mobile Legends',
            'product_name' => '86 Diamonds',
            'sku' => 'ML86',
            'target_no' => '123456 (1234)',
            'customer_phone' => '081234567890',
            'price' => 25000,
            'payment_method' => 'QRIS',
            'payment_status' => 'paid',
            'topup_status' => 'success',
            'is_synced_to_simup' => false,
        ]);

        $service = new SimupIntegrationService;
        $result = $service->syncTransaction($transaction);

        $this->assertTrue($result);
        $transaction->refresh();
        $this->assertTrue($transaction->is_synced_to_simup);
        $this->assertNotNull($transaction->synced_to_simup_at);

        Http::assertSent(function ($request) {
            return $request->url() == 'http://127.0.0.1:8000/api/v1/webhook/topup-income' &&
                $request->header('X-Wistek-Secret')[0] == 'wistek_simup_secret_key_2026' &&
                $request['kode_transaksi'] == 'INV-20260905-SIMUP01' &&
                $request['total'] == 25000;
        });
    }

    /**
     * Test batch syncing pending unsynced paid transactions.
     */
    public function test_batch_sync_pending_transactions()
    {
        Http::fake([
            '*/api/v1/webhook/topup-income' => Http::response([
                'success' => true,
                'message' => 'Already synced',
            ], 200),
        ]);

        Transaction::withoutEvents(function () {
            // Transaction 1: paid & unsynced -> should sync
            Transaction::create([
                'invoice' => 'INV-20260905-BATCH1',
                'category_name' => 'Mobile Legends',
                'product_name' => '86 Diamonds',
                'sku' => 'ML86',
                'target_no' => '123456',
                'price' => 15000,
                'payment_method' => 'DANA',
                'payment_status' => 'paid',
                'topup_status' => 'success',
                'is_synced_to_simup' => false,
            ]);

            // Transaction 2: unpaid -> should skip
            Transaction::create([
                'invoice' => 'INV-20260905-BATCH2',
                'category_name' => 'Free Fire',
                'product_name' => '100 Diamonds',
                'sku' => 'FF100',
                'target_no' => '654321',
                'price' => 20000,
                'payment_method' => 'QRIS',
                'payment_status' => 'unpaid',
                'topup_status' => 'pending',
                'is_synced_to_simup' => false,
            ]);
        });

        $t1 = Transaction::where('invoice', 'INV-20260905-BATCH1')->first();

        $service = new SimupIntegrationService;
        $result = $service->syncPendingTransactions();

        $this->assertEquals(1, $result['total']);
        $this->assertEquals(1, $result['success']);
        $this->assertEquals(0, $result['failed']);

        $t1->refresh();
        $this->assertTrue($t1->is_synced_to_simup);
    }
}
