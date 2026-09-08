<?php

namespace App\Services;

use App\Models\Setting;
use Exception;
use Illuminate\Support\Facades\Http;

class DokuService
{
    protected string $clientId;

    protected string $secretKey;

    protected string $mode;

    protected string $baseUrl;

    public function __construct()
    {
        $this->clientId = Setting::get('doku_client_id', env('DOKU_CLIENT_ID', ''));
        $this->secretKey = Setting::get('doku_secret_key', env('DOKU_SECRET_KEY', ''));
        $this->mode = Setting::get('doku_mode', env('DOKU_MODE', 'sandbox'));

        $this->baseUrl = ($this->mode === 'production')
            ? 'https://api.doku.com'
            : 'https://api-sandbox.doku.com';
    }

    /**
     * Get list of active payment channels from DOKU
     */
    public function getPaymentChannels(int $amount = 10000): array
    {
        return [
            [
                'code' => 'QRIS',
                'name' => 'QRIS DOKU (Semua E-Wallet)',
                'fee_flat' => 750,
                'fee_percent' => 0.7,
                'icon_url' => 'https://sandbox.doku.com/assets/images/payment-channel/qris.png',
            ],
            [
                'code' => 'BCAVA',
                'name' => 'BCA Virtual Account',
                'fee_flat' => 4500,
                'fee_percent' => 0,
                'icon_url' => 'https://sandbox.doku.com/assets/images/payment-channel/bca.png',
            ],
            [
                'code' => 'MANDIRIVA',
                'name' => 'Mandiri Virtual Account',
                'fee_flat' => 4500,
                'fee_percent' => 0,
                'icon_url' => 'https://sandbox.doku.com/assets/images/payment-channel/mandiri.png',
            ],
            [
                'code' => 'BNIVA',
                'name' => 'BNI Virtual Account',
                'fee_flat' => 4500,
                'fee_percent' => 0,
                'icon_url' => 'https://sandbox.doku.com/assets/images/payment-channel/bni.png',
            ],
            [
                'code' => 'BRIVA',
                'name' => 'BRI Virtual Account',
                'fee_flat' => 4500,
                'fee_percent' => 0,
                'icon_url' => 'https://sandbox.doku.com/assets/images/payment-channel/bri.png',
            ],
            [
                'code' => 'PERMATAVA',
                'name' => 'Permata Virtual Account',
                'fee_flat' => 4500,
                'fee_percent' => 0,
                'icon_url' => 'https://sandbox.doku.com/assets/images/payment-channel/permata.png',
            ],
            [
                'code' => 'CIMBVA',
                'name' => 'CIMB Niaga Virtual Account',
                'fee_flat' => 4500,
                'fee_percent' => 0,
                'icon_url' => 'https://sandbox.doku.com/assets/images/payment-channel/cimb.png',
            ],
            [
                'code' => 'ALFAMART',
                'name' => 'Alfamart',
                'fee_flat' => 5000,
                'fee_percent' => 0,
                'icon_url' => 'https://sandbox.doku.com/assets/images/payment-channel/alfamart.png',
            ],
            [
                'code' => 'INDOMARET',
                'name' => 'Indomaret',
                'fee_flat' => 5000,
                'fee_percent' => 0,
                'icon_url' => 'https://sandbox.doku.com/assets/images/payment-channel/indomaret.png',
            ],
            [
                'code' => 'DOKU_CHECKOUT',
                'name' => 'DOKU Checkout (All-in-One)',
                'fee_flat' => 0,
                'fee_percent' => 0,
                'icon_url' => 'https://sandbox.doku.com/assets/images/payment-channel/doku.png',
            ],
        ];
    }

    /**
     * Generate DOKU Jokul Signature using HMAC-SHA256
     */
    public function generateSignature(string $requestId, string $requestTimestamp, string $requestTarget, string $jsonPayload): string
    {
        $digest = base64_encode(hash('sha256', $jsonPayload, true));

        $rawSignatureComponent = "Client-Id:{$this->clientId}\n".
            "Request-Id:{$requestId}\n".
            "Request-Timestamp:{$requestTimestamp}\n".
            "Request-Target:{$requestTarget}\n".
            "Digest:{$digest}";

        return 'HMACSHA256='.base64_encode(hash_hmac('sha256', $rawSignatureComponent, $this->secretKey, true));
    }

    /**
     * Create Checkout Payment Request via DOKU Jokul API
     */
    public function createTransaction(string $invoice, string $productName, int $amount, ?string $customerPhone = null, ?string $paymentMethod = null): array
    {
        if (empty($this->clientId) || empty($this->secretKey)) {
            throw new Exception('DOKU Client ID atau Secret Key belum diatur di Pengaturan API.');
        }

        $requestTarget = '/checkout/v1/payment';
        $requestTimestamp = gmdate('Y-m-d\TH:i:s\Z');
        $requestId = $invoice;

        $payload = [
            'order' => [
                'invoice_number' => $invoice,
                'amount' => $amount,
                'line_items' => [
                    [
                        'name' => mb_substr($productName, 0, 50),
                        'price' => $amount,
                        'quantity' => 1,
                    ],
                ],
                'callback_url' => url('/transaction/'.$invoice),
                'notification_url' => url('/callback/doku'),
                'auto_redirect' => true,
            ],
            'payment' => [
                'payment_due_date' => 1440, // 24 hours in minutes
            ],
            'customer' => [
                'name' => 'Pelanggan Wistek',
                'phone' => $customerPhone ?: '081234567890',
                'email' => 'customer@wistektopup.com',
            ],
        ];

        $jsonPayload = json_encode($payload);
        $signature = $this->generateSignature($requestId, $requestTimestamp, $requestTarget, $jsonPayload);

        try {
            $response = Http::withHeaders([
                'Client-Id' => $this->clientId,
                'Request-Id' => $requestId,
                'Request-Timestamp' => $requestTimestamp,
                'Signature' => $signature,
                'Content-Type' => 'application/json',
            ])->timeout(10)->post($this->baseUrl.$requestTarget, $payload);

            if ($response->successful()) {
                $data = $response->json();
                $paymentUrl = $data['payment']['url'] ?? '';

                return [
                    'success' => true,
                    'payment_url' => $paymentUrl,
                    'reference' => $data['order']['invoice_number'] ?? $invoice,
                    'pay_code' => $data['payment']['pay_code'] ?? null,
                ];
            }

            $errorMsg = $response->json()['error']['message'] ?? $response->body();

            return [
                'success' => false,
                'message' => 'DOKU Error: '.$errorMsg,
            ];
        } catch (Exception $e) {
            logger()->error('DOKU createTransaction failed: '.$e->getMessage());

            return [
                'success' => false,
                'message' => $e->getMessage(),
            ];
        }
    }

    /**
     * Validate DOKU Webhook Callback Signature
     */
    public function validateCallbackSignature(
        string $jsonPayload,
        ?string $clientIdHeader,
        ?string $requestIdHeader,
        ?string $requestTimestampHeader,
        ?string $requestTargetHeader,
        ?string $signatureHeader
    ): bool {
        if (empty($this->secretKey) || empty($signatureHeader)) {
            return false;
        }

        $requestId = $requestIdHeader ?? '';
        $requestTimestamp = $requestTimestampHeader ?? '';
        $requestTarget = $requestTargetHeader ?? '/callback/doku';

        $expectedSignature = $this->generateSignature($requestId, $requestTimestamp, $requestTarget, $jsonPayload);

        return hash_equals($expectedSignature, $signatureHeader);
    }

    /**
     * Get connection status details for Admin Panel
     */
    public function getStatusDetails(): array
    {
        if (empty($this->clientId) || empty($this->secretKey)) {
            return [
                'success' => false,
                'message' => 'DOKU Client ID / Secret Key belum diisi',
            ];
        }

        return [
            'success' => true,
            'message' => 'DOKU Siap digunakan (Mode: '.ucfirst($this->mode).')',
        ];
    }
}
