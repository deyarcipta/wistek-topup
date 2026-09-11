<?php

namespace App\Services;

use App\Models\Setting;
use Exception;
use Illuminate\Http\Client\Response;
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
        $this->serverKey = trim((string) Setting::get('midtrans_server_key', env('MIDTRANS_SERVER_KEY', '')));
        $this->clientKey = trim((string) Setting::get('midtrans_client_key', env('MIDTRANS_CLIENT_KEY', '')));
        $this->mode = Setting::get('midtrans_mode', env('MIDTRANS_MODE', 'sandbox'));

        // Auto detect mode if serverKey starts with Mid-server- (Production) or SB-Mid-server- (Sandbox)
        if (str_starts_with($this->serverKey, 'Mid-server-')) {
            $this->mode = 'production';
        } elseif (str_starts_with($this->serverKey, 'SB-Mid-server-')) {
            $this->mode = 'sandbox';
        }

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
        if (empty($this->serverKey)) {
            return [];
        }

        $qrisFeePercent = TripayService::getServiceFeePercent($amount);

        return [
            [
                'code' => 'QRIS',
                'name' => 'QRIS (GoPay, OVO, Dana, LinkAja, ShopeePay)',
                'fee_flat' => 0,
                'fee_percent' => $qrisFeePercent,
                'min_fee' => 0,
                'max_fee' => 0,
                'icon_url' => url('/images/payments/qris.svg'),
            ],
            [
                'code' => 'BCAVA',
                'name' => 'BCA Virtual Account',
                'fee_flat' => 4000,
                'fee_percent' => 0,
                'min_fee' => 0,
                'max_fee' => 0,
                'icon_url' => url('/images/payments/bca.svg'),
            ],
            [
                'code' => 'BNIVA',
                'name' => 'BNI Virtual Account',
                'fee_flat' => 4000,
                'fee_percent' => 0,
                'min_fee' => 0,
                'max_fee' => 0,
                'icon_url' => url('/images/payments/bni.svg'),
            ],
            [
                'code' => 'BRIVA',
                'name' => 'BRI Virtual Account',
                'fee_flat' => 4000,
                'fee_percent' => 0,
                'min_fee' => 0,
                'max_fee' => 0,
                'icon_url' => url('/images/payments/bri.svg'),
            ],
            [
                'code' => 'MANDIRIVA',
                'name' => 'Mandiri Bill / VA',
                'fee_flat' => 4000,
                'fee_percent' => 0,
                'min_fee' => 0,
                'max_fee' => 0,
                'icon_url' => 'https://assets.tripay.co.id/upload/payment-icon/T9Z012UE331583531536.png',
            ],
            [
                'code' => 'PERMATAVA',
                'name' => 'Permata Virtual Account',
                'fee_flat' => 4000,
                'fee_percent' => 0,
                'min_fee' => 0,
                'max_fee' => 0,
                'icon_url' => 'https://assets.tripay.co.id/upload/payment-icon/qQYo61sIDa1702995837.png',
            ],
            [
                'code' => 'CIMBVA',
                'name' => 'CIMB Niaga Virtual Account',
                'fee_flat' => 4000,
                'fee_percent' => 0,
                'min_fee' => 0,
                'max_fee' => 0,
                'icon_url' => 'https://assets.tripay.co.id/upload/payment-icon/WtEJwfuphn1614003973.png',
            ],
            [
                'code' => 'GOPAY',
                'name' => 'GoPay QRIS',
                'fee_flat' => 0,
                'fee_percent' => 2.0,
                'min_fee' => 1000,
                'max_fee' => 0,
                'icon_url' => 'https://assets.tripay.co.id/upload/payment-icon/fH6Y7wDT171586199243.png',
            ],
            [
                'code' => 'SHOPEEPAY',
                'name' => 'ShopeePay QRIS',
                'fee_flat' => 0,
                'fee_percent' => 2.0,
                'min_fee' => 1000,
                'max_fee' => 0,
                'icon_url' => 'https://assets.tripay.co.id/upload/payment-icon/d204uajhlS1655719774.png',
            ],
            [
                'code' => 'ALFAMART',
                'name' => 'Alfamart',
                'fee_flat' => 5000,
                'fee_percent' => 0,
                'min_fee' => 0,
                'max_fee' => 0,
                'icon_url' => url('/images/payments/alfamart.svg'),
            ],
            [
                'code' => 'INDOMARET',
                'name' => 'Indomaret',
                'fee_flat' => 5000,
                'fee_percent' => 0,
                'min_fee' => 0,
                'max_fee' => 0,
                'icon_url' => url('/images/payments/indomaret.svg'),
            ],
            [
                'code' => 'MIDTRANS_SNAP',
                'name' => 'Midtrans Snap (Popup / Kartu Kredit)',
                'fee_flat' => 0,
                'fee_percent' => 0,
                'min_fee' => 0,
                'max_fee' => 0,
                'icon_url' => url('/images/payments/qris.svg'),
            ],
        ];
    }

    /**
     * Create Direct Charge via Midtrans Core API or Snap Token Fallback
     */
    public function createTransaction(string $invoice, string $productName, int $amount, ?string $customerPhone = null, ?string $paymentMethod = null): array
    {
        if (empty($this->serverKey)) {
            throw new Exception('Midtrans Server Key belum diatur di Pengaturan API Admin.');
        }

        $method = strtoupper((string) ($paymentMethod ?: 'QRIS'));

        $customerDetails = [
            'first_name' => 'Pelanggan',
            'last_name' => 'Wistek',
            'email' => 'customer@wistektopup.com',
            'phone' => $customerPhone ?: '081234567890',
        ];

        $itemDetails = [
            [
                'id' => $invoice,
                'price' => $amount,
                'quantity' => 1,
                'name' => mb_substr($productName, 0, 50),
            ],
        ];

        // 1. Direct Core Charge for QRIS / GOPAY / SHOPEEPAY
        if (in_array($method, ['QRIS', 'GOPAY', 'SHOPEEPAY']) || str_contains($method, 'QRIS')) {
            $payloadsToTry = [];

            // 1. Try GoPay Direct Charge (enabled by default on all Midtrans Sandbox/Production accounts)
            $payloadsToTry[] = [
                'payment_type' => 'gopay',
                'transaction_details' => [
                    'order_id' => $invoice,
                    'gross_amount' => $amount,
                ],
                'item_details' => $itemDetails,
                'customer_details' => $customerDetails,
                'gopay' => [
                    'enable_callback' => true,
                    'callback_url' => url('/transaction/'.$invoice),
                ],
            ];

            // 2. Try QRIS Direct Charge
            $payloadsToTry[] = [
                'payment_type' => 'qris',
                'transaction_details' => [
                    'order_id' => $invoice,
                    'gross_amount' => $amount,
                ],
                'item_details' => $itemDetails,
                'customer_details' => $customerDetails,
                'qris' => [
                    'acquirer' => 'gopay',
                ],
            ];

            $lastError = null;
            foreach ($payloadsToTry as $payload) {
                try {
                    $response = $this->sendChargeRequest($payload);

                    if ($response->successful()) {
                        $resData = $response->json();
                        $actions = $resData['actions'] ?? [];
                        $qrUrl = null;
                        $qrString = $resData['qr_string'] ?? null;

                        foreach ($actions as $action) {
                            if (($action['name'] ?? '') === 'generate-qr-code') {
                                $qrUrl = $action['url'] ?? null;
                                break;
                            }
                        }

                        if (empty($qrUrl) && ! empty($actions[0]['url'])) {
                            $qrUrl = $actions[0]['url'];
                        }

                        if (! empty($qrUrl) || ! empty($qrString)) {
                            return [
                                'success' => true,
                                'reference' => $resData['transaction_id'] ?? $invoice,
                                'qr_url' => $qrUrl,
                                'qr_string' => $qrString,
                                'qr_content' => $qrString ?: $qrUrl,
                                'expired_time' => (time() + (24 * 60 * 60)),
                            ];
                        }
                    } else {
                        $lastError = $response->json()['status_message'] ?? $response->json()['error_messages'][0] ?? $response->body();
                    }
                } catch (Exception $e) {
                    $lastError = $e->getMessage();
                }
            }

            $snapFallback = $this->createSnapFallback($invoice, $amount, $itemDetails, $customerDetails);
            if ($snapFallback) {
                return $snapFallback;
            }

            throw new Exception('Midtrans Direct QRIS Error: '.($lastError ?: 'Payment channel is not activated on Midtrans Sandbox dashboard.'));
        }

        // 2. Direct Core Charge for Virtual Accounts (BCA, BNI, BRI, Mandiri, Permata, CIMB)
        $vaBankMap = [
            'BCAVA' => 'bca',
            'BNIVA' => 'bni',
            'BRIVA' => 'bri',
            'CIMBVA' => 'cimb',
            'PERMATAVA' => 'permata',
        ];

        if (array_key_exists($method, $vaBankMap) || $method === 'MANDIRIVA') {
            if ($method === 'MANDIRIVA') {
                $payload = [
                    'payment_type' => 'echannel',
                    'transaction_details' => [
                        'order_id' => $invoice,
                        'gross_amount' => $amount,
                    ],
                    'item_details' => $itemDetails,
                    'customer_details' => $customerDetails,
                    'echannel' => [
                        'bill_info1' => 'Pembayaran:',
                        'bill_info2' => 'Topup Wistek',
                    ],
                ];
            } elseif ($method === 'PERMATAVA') {
                $payload = [
                    'payment_type' => 'permata',
                    'transaction_details' => [
                        'order_id' => $invoice,
                        'gross_amount' => $amount,
                    ],
                    'item_details' => $itemDetails,
                    'customer_details' => $customerDetails,
                ];
            } else {
                $bank = $vaBankMap[$method];
                $payload = [
                    'payment_type' => 'bank_transfer',
                    'transaction_details' => [
                        'order_id' => $invoice,
                        'gross_amount' => $amount,
                    ],
                    'item_details' => $itemDetails,
                    'customer_details' => $customerDetails,
                    'bank_transfer' => [
                        'bank' => $bank,
                    ],
                ];
            }

            $response = $this->sendChargeRequest($payload);

            if ($response->successful()) {
                $resData = $response->json();
                $payCode = null;

                if (isset($resData['va_numbers'][0]['va_number'])) {
                    $payCode = $resData['va_numbers'][0]['va_number'];
                } elseif (isset($resData['permata_va_number'])) {
                    $payCode = $resData['permata_va_number'];
                } elseif (isset($resData['bill_key'])) {
                    $billerCode = $resData['biller_code'] ?? '70012';
                    $payCode = 'Kode Perusahaan: '.$billerCode.' | Bill Key: '.$resData['bill_key'];
                }

                if (! empty($payCode)) {
                    return [
                        'success' => true,
                        'reference' => $resData['transaction_id'] ?? $invoice,
                        'pay_code' => $payCode,
                        'expired_time' => (time() + (24 * 60 * 60)),
                    ];
                }
            }

            $snapFallback = $this->createSnapFallback($invoice, $amount, $itemDetails, $customerDetails);
            if ($snapFallback) {
                return $snapFallback;
            }

            $errorMsg = $response->json()['status_message'] ?? $response->json()['error_messages'][0] ?? $response->body();
            throw new Exception('Midtrans Direct VA Error: '.$errorMsg);
        }

        // 3. Direct Charge for Convenience Store (Alfamart / Indomaret)
        if (in_array($method, ['ALFAMART', 'INDOMARET'])) {
            $store = strtolower($method);
            $payload = [
                'payment_type' => 'cstore',
                'transaction_details' => [
                    'order_id' => $invoice,
                    'gross_amount' => $amount,
                ],
                'item_details' => $itemDetails,
                'customer_details' => $customerDetails,
                'cstore' => [
                    'store' => $store,
                    'message' => 'Topup Wistek',
                ],
            ];

            $response = $this->sendChargeRequest($payload);

            if ($response->successful()) {
                $resData = $response->json();
                $payCode = $resData['payment_code'] ?? null;

                if (! empty($payCode)) {
                    return [
                        'success' => true,
                        'reference' => $resData['transaction_id'] ?? $invoice,
                        'pay_code' => $payCode,
                        'expired_time' => (time() + (24 * 60 * 60)),
                    ];
                }
            }

            $errorMsg = $response->json()['status_message'] ?? $response->json()['error_messages'][0] ?? $response->body();
            throw new Exception('Midtrans CStore Error: '.$errorMsg);
        }

        // 4. Snap Token API only if method is explicitly MIDTRANS_SNAP
        if ($method === 'MIDTRANS_SNAP') {
            $snapPayload = [
                'transaction_details' => [
                    'order_id' => $invoice,
                    'gross_amount' => $amount,
                ],
                'item_details' => $itemDetails,
                'customer_details' => $customerDetails,
                'callbacks' => [
                    'finish' => url('/transaction/'.$invoice),
                ],
                'override_notification_urls' => [
                    url('/callback/midtrans'),
                ],
            ];

            $snapResponse = Http::withBasicAuth($this->serverKey, '')
                ->withHeaders([
                    'Accept' => 'application/json',
                    'Content-Type' => 'application/json',
                ])
                ->post($this->snapBaseUrl.'transactions', $snapPayload);

            if ($snapResponse->successful()) {
                $snapData = $snapResponse->json();

                return [
                    'success' => true,
                    'payment_url' => $snapData['redirect_url'] ?? '',
                    'token' => $snapData['token'] ?? '',
                    'reference' => $invoice,
                    'expired_time' => (time() + (24 * 60 * 60)),
                ];
            }

            $errorMsg = $snapResponse->json()['error_messages'][0] ?? $snapResponse->body();
            throw new Exception('Midtrans Snap Error: '.$errorMsg);
        }

        throw new Exception('Metode pembayaran '.$method.' tidak didukung di Midtrans.');
    }

    /**
     * Check transaction status directly via Midtrans Status API
     */
    public function checkTransactionStatus(string $reference): array
    {
        if (empty($this->serverKey)) {
            return ['success' => false, 'message' => 'Server Key missing'];
        }

        try {
            $response = Http::withBasicAuth($this->serverKey, '')
                ->withHeaders(['Accept' => 'application/json'])
                ->get($this->apiBaseUrl.$reference.'/status');

            if ($response->successful()) {
                $data = $response->json();
                $status = $data['transaction_status'] ?? '';
                $fraud = $data['fraud_status'] ?? '';

                $isPaid = in_array($status, ['capture', 'settlement']) && $fraud !== 'challenge';
                $isFailed = in_array($status, ['cancel', 'deny', 'expire']);

                return [
                    'success' => true,
                    'status' => $status,
                    'paid' => $isPaid,
                    'failed' => $isFailed,
                    'payment_status' => $isPaid ? 'paid' : ($isFailed ? 'failed' : 'unpaid'),
                    'raw' => $data,
                ];
            }

            return ['success' => false, 'message' => $response->body()];
        } catch (Exception $e) {
            return ['success' => false, 'message' => $e->getMessage()];
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
     * Fallback to Snap Token creation if Direct Charge is not enabled on merchant account
     */
    protected function createSnapFallback(string $invoice, int $amount, array $itemDetails, array $customerDetails): ?array
    {
        try {
            $snapPayload = [
                'transaction_details' => [
                    'order_id' => $invoice,
                    'gross_amount' => $amount,
                ],
                'item_details' => $itemDetails,
                'customer_details' => $customerDetails,
                'callbacks' => [
                    'finish' => url('/transaction/'.$invoice),
                ],
                'override_notification_urls' => [
                    url('/callback/midtrans'),
                ],
            ];

            $snapResponse = Http::withBasicAuth($this->serverKey, '')
                ->withHeaders([
                    'Accept' => 'application/json',
                    'Content-Type' => 'application/json',
                ])
                ->post($this->snapBaseUrl.'transactions', $snapPayload);

            if ($snapResponse->successful()) {
                $snapData = $snapResponse->json();

                return [
                    'success' => true,
                    'payment_url' => $snapData['redirect_url'] ?? '',
                    'token' => $snapData['token'] ?? '',
                    'client_key' => $this->clientKey,
                    'snap_js' => $this->mode === 'production' ? 'https://app.midtrans.com/snap/snap.js' : 'https://app.sandbox.midtrans.com/snap/snap.js',
                    'reference' => $invoice,
                    'expired_time' => (time() + (24 * 60 * 60)),
                ];
            }
        } catch (Exception $e) {
            // Ignore snap fallback error
        }

        return null;
    }

    /**
     * Send charge request to Midtrans API with automatic environment fallback (Sandbox <-> Production)
     */
    protected function sendChargeRequest(array $payload): Response
    {
        $response = Http::withBasicAuth($this->serverKey, '')
            ->withHeaders([
                'Accept' => 'application/json',
                'Content-Type' => 'application/json',
            ])
            ->post($this->apiBaseUrl.'charge', $payload);

        if (! $response->successful() && ($response->status() === 401 || str_contains($response->body(), 'Unknown Merchant'))) {
            $altBaseUrl = str_contains($this->apiBaseUrl, 'sandbox')
                ? 'https://api.midtrans.com/v2/'
                : 'https://api.sandbox.midtrans.com/v2/';

            $altResponse = Http::withBasicAuth($this->serverKey, '')
                ->withHeaders([
                    'Accept' => 'application/json',
                    'Content-Type' => 'application/json',
                ])
                ->post($altBaseUrl.'charge', $payload);

            if ($altResponse->successful()) {
                return $altResponse;
            }
        }

        return $response;
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
            'message' => 'Midtrans Siap digunakan (Mode: '.ucfirst($this->mode).')',
        ];
    }
}
