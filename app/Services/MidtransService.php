<?php

namespace App\Services;

use App\Models\Setting;
use Exception;
use Illuminate\Support\Facades\Http;

class MidtransService
{
    protected string $serverKey;

    protected string $clientKey;

    protected string $mode;

    protected string $apiBaseUrl;

    protected string $snapBaseUrl;

    public function __construct()
    {
        $this->serverKey = Setting::get('midtrans_server_key', env('MIDTRANS_SERVER_KEY', ''));
        $this->clientKey = Setting::get('midtrans_client_key', env('MIDTRANS_CLIENT_KEY', ''));
        $this->mode = Setting::get('midtrans_mode', env('MIDTRANS_MODE', 'sandbox'));

        if ($this->mode === 'production') {
            $this->apiBaseUrl = 'https://api.midtrans.com/v2/';
            $this->snapBaseUrl = 'https://app.midtrans.com/snap/v1/';
        } else {
            $this->apiBaseUrl = 'https://api.sandbox.midtrans.com/v2/';
            $this->snapBaseUrl = 'https://app.sandbox.midtrans.com/snap/v1/';
        }
    }

    /**
     * Get list of standard payment channels available in Midtrans
     */
    public function getPaymentChannels(int $amount = 10000): array
    {
        return [
            ['code' => 'QRIS', 'name' => 'QRIS (GoPay, OVO, Dana, ShopeePay)', 'fee_flat' => 0, 'fee_percent' => 0.7, 'icon' => 'https://upload.wikimedia.org/wikipedia/commons/a/a2/Logo_QRIS.svg'],
            ['code' => 'BCAVA', 'name' => 'BCA Virtual Account', 'fee_flat' => 4000, 'fee_percent' => 0, 'icon' => 'https://upload.wikimedia.org/wikipedia/commons/5/5c/Bank_Central_Asia_logo.svg'],
            ['code' => 'MANDIRIVA', 'name' => 'Mandiri Bill Payment', 'fee_flat' => 4000, 'fee_percent' => 0, 'icon' => 'https://upload.wikimedia.org/wikipedia/commons/a/ad/Bank_Mandiri_logo_2016.svg'],
            ['code' => 'BNIVA', 'name' => 'BNI Virtual Account', 'fee_flat' => 4000, 'fee_percent' => 0, 'icon' => 'https://upload.wikimedia.org/wikipedia/id/5/55/BNI_logo.svg'],
            ['code' => 'BRIVA', 'name' => 'BRI Virtual Account', 'fee_flat' => 4000, 'fee_percent' => 0, 'icon' => 'https://upload.wikimedia.org/wikipedia/commons/2/2e/BRI_2020.svg'],
            ['code' => 'PERMATAVA', 'name' => 'Permata Virtual Account', 'fee_flat' => 4000, 'fee_percent' => 0, 'icon' => 'https://upload.wikimedia.org/wikipedia/commons/a/a6/Permata_Bank_logo.svg'],
            ['code' => 'INDOMARET', 'name' => 'Indomaret / Ceriamart', 'fee_flat' => 5000, 'fee_percent' => 0, 'icon' => 'https://upload.wikimedia.org/wikipedia/commons/9/9d/Logo_Indomaret.png'],
        ];
    }

    /**
     * Create Snap Token / Redirect URL or Charge via Midtrans
     */
    public function createTransaction(string $invoice, string $productName, int $amount, ?string $customerPhone = null, ?string $paymentMethod = null): array
    {
        if (empty($this->serverKey)) {
            throw new Exception('Midtrans Server Key belum diatur di Pengaturan API.');
        }

        $payload = [
            'transaction_details' => [
                'order_id' => $invoice,
                'gross_amount' => $amount,
            ],
            'item_details' => [
                [
                    'id' => $invoice,
                    'price' => $amount,
                    'quantity' => 1,
                    'name' => mb_substr($productName, 0, 50),
                ],
            ],
            'customer_details' => [
                'first_name' => 'Pelanggan',
                'last_name' => 'Wistek',
                'email' => 'customer@wistektopup.com',
                'phone' => $customerPhone ?: '081234567890',
            ],
            'callbacks' => [
                'finish' => url('/transaction/'.$invoice),
            ],
            'override_notification_urls' => [
                url('/callback/midtrans'),
            ],
        ];

        try {
            $response = Http::withBasicAuth($this->serverKey, '')
                ->withHeaders([
                    'Accept' => 'application/json',
                    'Content-Type' => 'application/json',
                ])
                ->post($this->snapBaseUrl.'transactions', $payload);

            if ($response->successful()) {
                $data = $response->json();
                $paymentUrl = $data['redirect_url'] ?? '';

                return [
                    'success' => true,
                    'payment_url' => $paymentUrl,
                    'token' => $data['token'] ?? '',
                    'reference' => $invoice,
                ];
            }

            $errorMsg = $response->json()['error_messages'][0] ?? $response->body();
            throw new Exception('Midtrans Error: '.$errorMsg);
        } catch (Exception $e) {
            logger()->error('Midtrans createTransaction failed: '.$e->getMessage());

            throw $e;
        }
    }

    /**
     * Validate Midtrans webhook signature key
     */
    public function validateCallbackSignature(string $orderId, string $statusCode, string $grossAmount, string $signatureKey): bool
    {
        if (empty($this->serverKey)) {
            return false;
        }

        $expectedSignature = hash('sha512', $orderId.$statusCode.$grossAmount.$this->serverKey);

        return hash_equals($expectedSignature, $signatureKey);
    }

    /**
     * Get connection status details for Admin Panel
     */
    public function getStatusDetails(): array
    {
        if (empty($this->serverKey)) {
            return [
                'success' => false,
                'message' => 'Midtrans Server Key belum diisi',
            ];
        }

        return [
            'success' => true,
            'message' => 'Midtrans Siap diganti (Mode: '.ucfirst($this->mode).')',
        ];
    }
}
