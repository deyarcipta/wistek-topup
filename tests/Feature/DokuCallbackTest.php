<?php

namespace Tests\Feature;

use App\Models\Transaction;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DokuCallbackTest extends TestCase
{
    use RefreshDatabase;

    public function test_doku_callback_get_ping()
    {
        $response = $this->get('/callback/doku');

        $response->assertStatus(200);
        $response->assertJson([
            'success' => true,
            'message' => 'DOKU Webhook Endpoint is Active.',
        ]);
    }

    public function test_doku_callback_post_jokul_format_success()
    {
        $transaction = Transaction::create([
            'invoice' => 'INV-DOKU-TEST-001',
            'customer_phone' => '081234567890',
            'category_name' => 'Mobile Legends',
            'product_name' => '86 Diamonds',
            'target_no' => '123456 (1234)',
            'sku' => 'ML86',
            'amount' => 20000,
            'price' => 20000,
            'payment_status' => 'unpaid',
            'topup_status' => 'pending',
            'payment_method' => 'DOKU',
        ]);

        $payload = [
            'order' => [
                'invoice_number' => 'INV-DOKU-TEST-001',
                'amount' => 20000,
            ],
            'transaction' => [
                'status' => 'SUCCESS',
                'id' => 'REF-DOKU-12345',
            ],
        ];

        $response = $this->postJson('/callback/doku', $payload);

        $response->assertStatus(200);
        $response->assertJson([
            'responseCode' => '2002500',
            'responseMessage' => 'Success',
        ]);

        $transaction->refresh();
        $this->assertEquals('paid', $transaction->payment_status);
    }

    public function test_doku_callback_post_snap_format_success()
    {
        $transaction = Transaction::create([
            'invoice' => 'INV-DOKU-SNAP-002',
            'customer_phone' => '081234567890',
            'category_name' => 'Mobile Legends',
            'product_name' => '86 Diamonds',
            'target_no' => '123456 (1234)',
            'sku' => 'ML86',
            'amount' => 20000,
            'price' => 20000,
            'payment_status' => 'unpaid',
            'topup_status' => 'pending',
            'payment_method' => 'DOKU',
        ]);

        $payload = [
            'originalPartnerReferenceNo' => 'INV-DOKU-SNAP-002',
            'originalReferenceNo' => 'REF-SNAP-999',
            'latestTransactionStatus' => '00',
            'transactionStatusDesc' => 'Success',
        ];

        $response = $this->postJson('/callback/doku', $payload);

        $response->assertStatus(200);
        $response->assertJson([
            'responseCode' => '2002500',
            'responseMessage' => 'Success',
        ]);

        $transaction->refresh();
        $this->assertEquals('paid', $transaction->payment_status);
    }
}
