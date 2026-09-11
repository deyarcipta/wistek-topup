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
            throw new Exception('Midtrans Server Key belum diatur di Pengaturan API.');
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

        // 1. Direct Charge for QRIS / GOPAY / SHOPEEPAY
        if (in_array($method, ['QRIS', 'GOPAY', 'SHOPEEPAY']) || str_contains($method, 'QRIS')) {
            $chargeType = str_contains($method, 'SHOPEE') ? 'shopeepay' : 'gopay';

            $payload = [
                'payment_type' => $chargeType,
                'transaction_details' => [
                    'order_id' => $invoice,
                    'gross_amount' => $amount,
                ],
                'item_details' => $itemDetails,
                'customer_details' => $customerDetails,
            ];

            if ($chargeType === 'gopay') {
                $payload['gopay'] = [
                    'enable_callback' => true,
                    'callback_url' => url('/transaction/'.$invoice),
                ];
            } else {
                $payload['shopeepay'] = [
                    'callback_url' => url('/transaction/'.$invoice),
                ];
            }

            try {
                $response = Http::withBasicAuth($this->serverKey, '')
                    ->withHeaders([
                        'Accept' => 'application/json',
                        'Content-Type' => 'application/json',
                    ])
                    ->post($this->apiBaseUrl.'charge', $payload);

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
                            'qr_string' => $qrString ?: $qrUrl,
                            'qr_content' => $qrUrl ?: $qrString,
                            'expired_time' => (time() + (24 * 60 * 60)),
                        ];
                    }
                }
            } catch (Exception $e) {
                logger()->error('Midtrans QRIS Charge error: '.$e->getMessage());
            }
        }

        // 2. Direct Charge for Virtual Accounts (BCA, BNI, BRI, Mandiri, Permata, CIMB)
        $vaBankMap = [
            'BCAVA' => 'bca',
            'BNIVA' => 'bni',
            'BRIVA' => 'bri',
            'CIMBVA' => 'cimb',
            'PERMATAVA' => 'permata',
        ];

        if (array_key_exists($method, $vaBankMap) || $method === 'MANDIRIVA') {
            try {
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

                $response = Http::withBasicAuth($this->serverKey, '')
                    ->withHeaders([
                        'Accept' => 'application/json',
                        'Content-Type' => 'application/json',
                    ])
                    ->post($this->apiBaseUrl.'charge', $payload);

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
            } catch (Exception $e) {
                logger()->error('Midtrans VA Charge error: '.$e->getMessage());
            }
        }

        // 3. Direct Charge for Convenience Store (Alfamart / Indomaret)
        if (in_array($method, ['ALFAMART', 'INDOMARET'])) {
            try {
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

                $response = Http::withBasicAuth($this->serverKey, '')
                    ->withHeaders([
                        'Accept' => 'application/json',
                        'Content-Type' => 'application/json',
                    ])
                    ->post($this->apiBaseUrl.'charge', $payload);

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
            } catch (Exception $e) {
                logger()->error('Midtrans CStore Charge error: '.$e->getMessage());
            }
        }

        // 4. Snap Token API Fallback
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

        try {
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
            throw new Exception('Midtrans Error: '.$errorMsg);
        } catch (Exception $e) {
            logger()->error('Midtrans createTransaction failed: '.$e->getMessage());

            throw $e;
        }
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
