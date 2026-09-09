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

                $qrisShare = (int) Setting::get('tripay_qris_fee_share', 50);
                $freeMinAmount = (int) Setting::get('tripay_qris_free_min_amount', 100000);

                foreach ($channels as $ch) {
                    if (! ($ch['active'] ?? false)) {
                        continue;
                    }
                    $code = $ch['code'] ?? '';
                    if (empty($code)) {
                        continue;
                    }

                    $isQris = ($code === 'QRIS' || str_contains($code, 'QRIS'));

                    // Respect TriPay merchant dashboard settings & tripay_qris_fee_share setting
                    $feeCustomer = $ch['fee_customer'] ?? [];
                    $totalFee = $ch['total_fee'] ?? $ch['fee_merchant'] ?? [];

                    $feeFlat = (int) ($feeCustomer['flat'] ?? 0);
                    $feePercent = (float) ($feeCustomer['percent'] ?? 0);

                    if ($isQris && $feeFlat == 0 && $feePercent == 0) {
                        $baseFlat = (int) ($totalFee['flat'] ?? 750);
                        $basePercent = (float) ($totalFee['percent'] ?? 0.7);

                        $feeFlat = (int) round($baseFlat * ($qrisShare / 100));
                        $feePercent = round($basePercent * ($qrisShare / 100), 2);
                    }

                    // Free QRIS Service Fee threshold check
                    if ($isQris && $freeMinAmount > 0 && $amount >= $freeMinAmount) {
                        $feeFlat = 0;
                        $feePercent = 0;
                    }

                    $minFee = isset($ch['minimum_fee']) ? (int) $ch['minimum_fee'] : 0;
                    $maxFee = isset($ch['maximum_fee']) ? (int) $ch['maximum_fee'] : 0;

                    $mapped[] = [
                        'code' => $code,
                        'name' => $ch['name'] ?? $code,
                        'fee_flat' => $feeFlat,
                        'fee_percent' => $feePercent,
                        'min_fee' => $minFee,
                        'max_fee' => $maxFee,
                        'free_min_amount' => $isQris ? $freeMinAmount : 0,
                        'icon' => $ch['icon_url'] ?? '',
                        'icon_url' => $ch['icon_url'] ?? '',
                    ];
                }

                return count($mapped) > 0 ? $mapped : $this->getFallbackChannels($amount);
            }
        } catch (Exception $e) {
            logger()->error('Tripay getPaymentChannels error: '.$e->getMessage());
        }

        return $this->getFallbackChannels($amount);
    }

    protected function getFallbackChannels(int $amount = 10000): array
    {
        $qrisShare = (int) Setting::get('tripay_qris_fee_share', 50);
        $freeMinAmount = (int) Setting::get('tripay_qris_free_min_amount', 100000);

        $qrisFeeFlat = (int) round(750 * ($qrisShare / 100));
        $qrisFeePercent = round(0.7 * ($qrisShare / 100), 2);

        if ($freeMinAmount > 0 && $amount >= $freeMinAmount) {
            $qrisFeeFlat = 0;
            $qrisFeePercent = 0;
        }

        return [
            ['code' => 'QRIS', 'name' => 'QRIS (Instant)', 'fee_flat' => $qrisFeeFlat, 'fee_percent' => $qrisFeePercent, 'min_fee' => 0, 'max_fee' => 0, 'free_min_amount' => $freeMinAmount, 'icon' => url('/images/payments/qris.svg')],
            ['code' => 'BCAVA', 'name' => 'BCA Virtual Account', 'fee_flat' => 5500, 'fee_percent' => 0, 'min_fee' => 0, 'max_fee' => 0, 'icon' => url('/images/payments/bca.svg')],
            ['code' => 'BNIVA', 'name' => 'BNI Virtual Account', 'fee_flat' => 4250, 'fee_percent' => 0, 'min_fee' => 0, 'max_fee' => 0, 'icon' => url('/images/payments/bni.svg')],
            ['code' => 'BRIVA', 'name' => 'BRI Virtual Account', 'fee_flat' => 4250, 'fee_percent' => 0, 'min_fee' => 0, 'max_fee' => 0, 'icon' => url('/images/payments/bri.svg')],
            ['code' => 'MANDIRIVA', 'name' => 'Mandiri Virtual Account', 'fee_flat' => 4250, 'fee_percent' => 0, 'min_fee' => 0, 'max_fee' => 0, 'icon' => 'https://assets.tripay.co.id/upload/payment-icon/T9Z012UE331583531536.png'],
            ['code' => 'CIMBVA', 'name' => 'CIMB Niaga Virtual Account', 'fee_flat' => 4250, 'fee_percent' => 0, 'min_fee' => 0, 'max_fee' => 0, 'icon' => 'https://assets.tripay.co.id/upload/payment-icon/WtEJwfuphn1614003973.png'],
            ['code' => 'BSIVA', 'name' => 'BSI Virtual Account', 'fee_flat' => 4250, 'fee_percent' => 0, 'min_fee' => 0, 'max_fee' => 0, 'icon' => 'https://assets.tripay.co.id/upload/payment-icon/tEclz5Assb1643375216.png'],
            ['code' => 'DANAMONVA', 'name' => 'Danamon Virtual Account', 'fee_flat' => 4250, 'fee_percent' => 0, 'min_fee' => 0, 'max_fee' => 0, 'icon' => 'https://assets.tripay.co.id/upload/payment-icon/F3pGzDOLUz1644245546.png'],
            ['code' => 'OTHERBANKVA', 'name' => 'Other Bank Virtual Account', 'fee_flat' => 4250, 'fee_percent' => 0, 'min_fee' => 0, 'max_fee' => 0, 'icon' => 'https://assets.tripay.co.id/upload/payment-icon/qQYo61sIDa1702995837.png'],
            ['code' => 'OVO', 'name' => 'OVO', 'fee_flat' => 0, 'fee_percent' => 3.0, 'min_fee' => 1000, 'max_fee' => 0, 'icon' => 'https://assets.tripay.co.id/upload/payment-icon/fH6Y7wDT171586199243.png'],
            ['code' => 'DANA', 'name' => 'DANA', 'fee_flat' => 0, 'fee_percent' => 3.0, 'min_fee' => 1000, 'max_fee' => 0, 'icon' => 'https://assets.tripay.co.id/upload/payment-icon/sj3UHLu8Tu1655719621.png'],
            ['code' => 'SHOPEEPAY', 'name' => 'ShopeePay', 'fee_flat' => 0, 'fee_percent' => 3.0, 'min_fee' => 1000, 'max_fee' => 0, 'icon' => 'https://assets.tripay.co.id/upload/payment-icon/d204uajhlS1655719774.png'],
            ['code' => 'ALFAMART', 'name' => 'Alfamart', 'fee_flat' => 3500, 'fee_percent' => 0, 'min_fee' => 0, 'max_fee' => 0, 'icon' => url('/images/payments/alfamart.svg')],
            ['code' => 'INDOMARET', 'name' => 'Indomaret', 'fee_flat' => 3500, 'fee_percent' => 0, 'min_fee' => 0, 'max_fee' => 0, 'icon' => url('/images/payments/indomaret.svg')],
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
