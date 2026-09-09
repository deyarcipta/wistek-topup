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
        if (empty($this->apiKey) || empty($this->privateKey) || empty($this->merchantCode)) {
            return [];
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
            ['code' => 'QRIS', 'name' => 'QRIS (Instant)', 'fee_flat' => 750, 'fee_percent' => 0.7, 'icon' => url('/images/payments/qris.svg')],
            ['code' => 'BCAVA', 'name' => 'BCA Virtual Account', 'fee_flat' => 4250, 'fee_percent' => 0, 'icon' => url('/images/payments/bca.svg')],
            ['code' => 'BNIVA', 'name' => 'BNI Virtual Account', 'fee_flat' => 4250, 'fee_percent' => 0, 'icon' => url('/images/payments/bni.svg')],
            ['code' => 'BRIVA', 'name' => 'BRI Virtual Account', 'fee_flat' => 4250, 'fee_percent' => 0, 'icon' => url('/images/payments/bri.svg')],
            ['code' => 'ALFAMART', 'name' => 'Alfamart', 'fee_flat' => 3500, 'fee_percent' => 0, 'icon' => url('/images/payments/alfamart.svg')],
            ['code' => 'INDOMARET', 'name' => 'Indomaret', 'fee_flat' => 3500, 'fee_percent' => 0, 'icon' => url('/images/payments/indomaret.svg')],
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
            'return_url' => url('/transaction/'.$invoice),
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

                $qrUrl = $data['qr_url'] ?? null;
                $qrString = $data['qr_string'] ?? $data['qr_content'] ?? null;

                return [
                    'success' => true,
                    'payment_url' => $data['checkout_url'] ?? $data['pay_url'] ?? '',
                    'reference' => $data['reference'] ?? $invoice,
                    'qr_url' => $qrUrl,
                    'qr_string' => $qrString,
                    'qr_content' => $qrString ?: $qrUrl,
                    'pay_code' => $data['pay_code'] ?? null,
                    'instructions' => $data['instructions'] ?? [],
                    'expired_time' => $data['expired_time'] ?? (time() + (24 * 60 * 60)),
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
     * Check transaction status directly via Tripay API
     */
    public function checkTransactionStatus(string $reference): array
    {
        if (empty($this->apiKey)) {
            return ['success' => false, 'message' => 'API Key missing'];
        }

        try {
            $response = Http::withHeaders([
                'Authorization' => 'Bearer '.$this->apiKey,
            ])->timeout(5)->get($this->baseUrl.'transaction/detail', [
                'reference' => $reference,
            ]);

            if ($response->successful()) {
                $data = $response->json()['data'] ?? [];
                $status = strtoupper((string) ($data['status'] ?? ''));

                return [
                    'success' => true,
                    'status' => $status,
                    'is_paid' => $status === 'PAID',
                    'is_failed' => in_array($status, ['EXPIRED', 'FAILED', 'REFUND']),
                    'reference' => $data['reference'] ?? $reference,
                    'paid_at' => $data['paid_at'] ?? null,
                    'raw' => $data,
                ];
            }

            return ['success' => false, 'message' => 'HTTP '.$response->status().' - '.$response->body()];
        } catch (Exception $e) {
            logger()->error('Tripay checkTransactionStatus error: '.$e->getMessage());

            return ['success' => false, 'message' => $e->getMessage()];
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
