<?php

namespace App\Services;

use App\Models\Setting;
use Exception;
use Illuminate\Support\Facades\Http;

class TripayService
{
    protected string $merchantCode;

    protected string $apiKey;

    protected string $privateKey;

    protected string $mode;

    protected string $baseUrl;

    // Define mapping from frontend codes to Tripay channel codes
    protected array $methodMapping = [
        'QRIS' => 'QRIS',
        'SHOPEEPAY' => 'SHOPEEPAY',
        'OVO' => 'OVO',
        'DANA' => 'DANA',
        'BCAVA' => 'BCAVA',
        'MANDIRIVA' => 'MYBVA', // or MYBVA / MANDIRIVA
        'BNIVA' => 'BNIVA',
        'BRIVA' => 'BRIVA',
        'PERMATAVA' => 'PERMATAVA',
        'CIMBVA' => 'CIMBVA',
        'ALFAMART' => 'ALFAMART',
        'INDOMARET' => 'INDOMARET',
    ];

    public function __construct()
    {
        $this->merchantCode = Setting::get('tripay_merchant_code', env('TRIPAY_MERCHANT_CODE', ''));
        $this->apiKey = Setting::get('tripay_api_key', env('TRIPAY_API_KEY', ''));
        $this->privateKey = Setting::get('tripay_private_key', env('TRIPAY_PRIVATE_KEY', ''));
        $this->mode = Setting::get('tripay_mode', env('TRIPAY_MODE', 'sandbox'));

        $this->baseUrl = ($this->mode === 'production')
            ? 'https://tripay.co.id/api/'
            : 'https://tripay.co.id/api-sandbox/';
    }

    /**
     * Get list of active payment channels from Tripay
     */
    public function getPaymentChannels(int $amount = 10000): array
    {
        if (empty($this->apiKey)) {
            return $this->getFallbackChannels();
        }

        try {
            $response = Http::withHeaders([
                'Authorization' => 'Bearer '.$this->apiKey,
            ])->timeout(5)->get($this->baseUrl.'merchant/payment-channel');

            if ($response->successful()) {
                $data = $response->json();
                $channels = $data['data'] ?? [];
                $mapped = [];

                foreach ($channels as $ch) {
                    if (! ($ch['active'] ?? false)) {
                        continue;
                    }
                    $code = $ch['code'] ?? '';
                    $feeFlat = (int) ($ch['total_fee']['flat'] ?? 0);
                    $feePercent = (float) ($ch['total_fee']['percent'] ?? 0);

                    $mapped[] = [
                        'code' => $code,
                        'name' => $ch['name'] ?? $code,
                        'fee_flat' => $feeFlat,
                        'fee_percent' => $feePercent,
                        'icon' => $ch['icon_url'] ?? '',
                    ];
                }

                return count($mapped) > 0 ? $mapped : $this->getFallbackChannels();
            }
        } catch (Exception $e) {
            logger()->error('Tripay getPaymentChannels error: '.$e->getMessage());
        }

        return $this->getFallbackChannels();
    }

    protected function getFallbackChannels(): array
    {
        return [
            ['code' => 'QRIS', 'name' => 'QRIS (Instant)', 'fee_flat' => 750, 'fee_percent' => 0.7, 'icon' => 'https://upload.wikimedia.org/wikipedia/commons/a/a2/Logo_QRIS.svg'],
            ['code' => 'BCAVA', 'name' => 'BCA Virtual Account', 'fee_flat' => 4250, 'fee_percent' => 0, 'icon' => 'https://upload.wikimedia.org/wikipedia/commons/5/5c/Bank_Central_Asia_logo.svg'],
            ['code' => 'BNIVA', 'name' => 'BNI Virtual Account', 'fee_flat' => 4250, 'fee_percent' => 0, 'icon' => 'https://upload.wikimedia.org/wikipedia/id/5/55/BNI_logo.svg'],
            ['code' => 'BRIVA', 'name' => 'BRI Virtual Account', 'fee_flat' => 4250, 'fee_percent' => 0, 'icon' => 'https://upload.wikimedia.org/wikipedia/commons/2/2e/BRI_2020.svg'],
            ['code' => 'ALFAMART', 'name' => 'Alfamart', 'fee_flat' => 3500, 'fee_percent' => 0, 'icon' => 'https://upload.wikimedia.org/wikipedia/commons/8/86/Alfamart_logo.svg'],
            ['code' => 'INDOMARET', 'name' => 'Indomaret', 'fee_flat' => 3500, 'fee_percent' => 0, 'icon' => 'https://upload.wikimedia.org/wikipedia/commons/9/9d/Logo_Indomaret.png'],
        ];
    }

    /**
     * Create Closed Transaction via Tripay
     */
    public function createTransaction(string $invoice, string $productName, int $amount, ?string $customerPhone = null, ?string $paymentMethod = null): array
    {
        if (empty($this->apiKey) || empty($this->privateKey) || empty($this->merchantCode)) {
            throw new Exception('Tripay Merchant Code / API Key / Private Key belum diatur.');
        }

        $method = $paymentMethod ?: 'QRIS';
        // Compute HMAC SHA256 Signature: merchant_code + merchant_ref + amount
        $signature = hash_hmac('sha256', $this->merchantCode.$invoice.$amount, $this->privateKey);

        $payload = [
            'method' => $method,
            'merchant_ref' => $invoice,
            'amount' => $amount,
            'customer_name' => 'Pelanggan Wistek',
            'customer_email' => 'customer@wistektopup.com',
            'customer_phone' => $customerPhone ?: '081234567890',
            'order_items' => [
                [
                    'name' => mb_substr($productName, 0, 50),
                    'price' => $amount,
                    'quantity' => 1,
                ],
            ],
            'callback_url' => url('/callback/tripay'),
            'return_url' => url('/'),
            'expired_time' => (time() + (24 * 60 * 60)), // 24 hours
            'signature' => $signature,
        ];

        try {
            $response = Http::withHeaders([
                'Authorization' => 'Bearer '.$this->apiKey,
                'Accept' => 'application/json',
            ])->post($this->baseUrl.'transaction/create', $payload);

            if ($response->successful()) {
                $data = $response->json()['data'] ?? [];

                return [
                    'success' => true,
                    'payment_url' => $data['checkout_url'] ?? '',
                    'reference' => $data['reference'] ?? $invoice,
                    'qr_content' => $data['qr_content'] ?? null,
                    'pay_code' => $data['pay_code'] ?? null,
                ];
            }

            $msg = $response->json()['message'] ?? $response->body();
            throw new Exception('Tripay Error: '.$msg);
        } catch (Exception $e) {
            logger()->error('Tripay createTransaction failed: '.$e->getMessage());

            throw $e;
        }
    }

    /**
     * Validate Tripay webhook HMAC SHA256 Signature
     */
    public function validateCallbackSignature(string $jsonPayload, ?string $signatureHeader): bool
    {
        if (empty($this->privateKey) || empty($signatureHeader)) {
            return false;
        }

        $expectedSignature = hash_hmac('sha256', $jsonPayload, $this->privateKey);

        return hash_equals($expectedSignature, $signatureHeader);
    }

    /**
     * Get connection status details for Admin Panel
     */
    public function getStatusDetails(): array
    {
        if (empty($this->apiKey) || empty($this->merchantCode)) {
            return [
                'success' => false,
                'message' => 'Tripay Merchant Code / API Key belum diisi',
            ];
        }

        return [
            'success' => true,
            'message' => 'Tripay Siap digunakan (Mode: '.ucfirst($this->mode).')',
        ];
    }
}
