<?php

namespace App\Services;

use App\Models\Setting;
use Duitku\Api;
use Duitku\Config;
use Exception;

class DuitkuService
{
    protected $merchantCode;

    protected $apiKey;

    protected $baseUrl;

    protected $config;

    // Define bidirectional mapping of codes
    protected $methodMapping = [
        // QRIS & E-Wallet
        'QRIS' => 'NQ',
        'SHOPEEPAY' => 'SP',
        'SHOPEEPAY_APP' => 'SA',
        'OVO' => 'OV',
        'DANA' => 'DA',
        'LINKAJA' => 'LA',
        'LINKAJA_QRIS' => 'LQ',
        'GUDANG_VOUCHER_QRIS' => 'GQ',
        'JENIUS_PAY' => 'JP',

        // Virtual Accounts
        'BCAVA' => 'BC',
        'MANDIRIVA' => 'M2',
        'BNIVA' => 'I1',
        'BRIVA' => 'BR',
        'PERMATAVA' => 'BT',
        'CIMBVA' => 'B1',
        'ATM_BERSAMA_VA' => 'A1',
        'MAYBANKVA' => 'VA',
        'BSIVA' => 'BV',
        'AGVA' => 'AG',
        'SAMPOERNAVA' => 'S1',
        'NOBUVA' => 'NC',

        // Retail Outlets
        'RETAIL' => 'FT',
        'INDOMARET' => 'IR',

        // Others
        'CREDIT_CARD' => 'VC',
        'INDODANA_PAYLATER' => 'DN',
    ];

    public function __construct()
    {
        $this->merchantCode = Setting::get('duitku_merchant_code', env('DUITKU_MERCHANT_CODE'));
        $this->apiKey = Setting::get('duitku_api_key', env('DUITKU_API_KEY'));

        $mode = Setting::get('duitku_mode', env('DUITKU_MODE', 'sandbox'));
        $this->baseUrl = $mode === 'production'
            ? 'https://passport.duitku.com/webapi/api/merchant/'
            : 'https://sandbox.duitku.com/webapi/api/merchant/';

        $isSandbox = ($mode !== 'production');
        // Initialize Duitku SDK Config (API Key, Merchant Code, Sandbox Mode, Sanitized Mode, Duitku Logs)
        $this->config = new Config($this->apiKey, $this->merchantCode, $isSandbox, true, false);
    }

    /**
     * Get list of active payment channels from Duitku
     */
    public function getPaymentChannels(int $amount = 10000): array
    {
        try {
            $response = Api::getPaymentMethod($amount, $this->config);
            $data = json_decode($response, true);

            // If it is an error response format
            if (isset($data['error']) || isset($data['statusMessage'])) {
                throw new Exception($data['statusMessage'] ?? 'Unknown getpaymentmethod error');
            }

            $rawChannels = $data['paymentFee'] ?? $data ?? [];
            $mappedChannels = [];

            // Find key mapping back (Duitku code to frontend code)
            $reverseMapping = array_flip($this->methodMapping);

            foreach ($rawChannels as $channel) {
                $duitkuCode = $channel['paymentMethod'] ?? '';
                $appCode = $reverseMapping[$duitkuCode] ?? $duitkuCode;

                $mappedChannels[] = [
                    'code' => $appCode,
                    'name' => $channel['paymentName'] ?? $duitkuCode,
                    'icon_url' => $channel['paymentImage'] ?? '',
                    'fee_flat' => (int) ($channel['totalFee'] ?? 0),
                    'fee_percent' => 0,
                ];
            }

            return $mappedChannels;
        } catch (Exception $e) {
            logger()->error('Duitku getPaymentChannels failed: '.$e->getMessage());

            // Return default/fallback channels if offline
            return [
                [
                    'code' => 'QRIS',
                    'name' => 'QRIS (Semua E-Wallet)',
                    'icon_url' => 'https://images.duitku.com/QD/qris.png',
                    'fee_flat' => 0,
                    'fee_percent' => 0.7,
                ],
                [
                    'code' => 'BCAVA',
                    'name' => 'BCA Virtual Account',
                    'icon_url' => 'https://images.duitku.com/QD/bca.png',
                    'fee_flat' => 1500,
                    'fee_percent' => 0,
                ],
                [
                    'code' => 'MANDIRIVA',
                    'name' => 'Mandiri Virtual Account',
                    'icon_url' => 'https://images.duitku.com/QD/mandiri.png',
                    'fee_flat' => 1500,
                    'fee_percent' => 0,
                ],
            ];
        }
    }

    /**
     * Create transaction / request invoice from Duitku (Inquiry V2)
     */
    public function createTransaction(string $invoice, string $productName, int $price, string $method, string $phone = '081234567890'): array
    {
        // Map app method code back to Duitku code (e.g. BCAVA -> BC)
        $duitkuMethod = $this->methodMapping[$method] ?? $method;

        $payload = [
            'paymentAmount' => $price,
            'paymentMethod' => $duitkuMethod,
            'merchantOrderId' => $invoice,
            'productDetails' => $productName,
            'additionalParam' => '',
            'merchantUserInfo' => 'customer@wistek.id',
            'customerVaName' => 'Pelanggan Wistek',
            'email' => 'customer@wistek.id',
            'phoneNumber' => $phone,
            'itemDetails' => [
                [
                    'name' => $productName,
                    'price' => $price,
                    'quantity' => 1,
                ],
            ],
            'callbackUrl' => url('/callback/duitku'),
            'returnUrl' => url('/transaction/'.$invoice),
            'expiryPeriod' => 1440, // 24 hours expiry in minutes
        ];

        try {
            $response = Api::createInvoice($payload, $this->config);
            $data = json_decode($response, true);

            if (isset($data['statusCode']) && $data['statusCode'] === '00') {
                return [
                    'success' => true,
                    'data' => [
                        'reference' => $data['reference'] ?? null,
                        'pay_code' => $data['vaNumber'] ?? null,
                        'qr_string' => $data['qrString'] ?? null,
                        'payment_url' => $data['paymentUrl'] ?? null,
                        'expired_time' => time() + (24 * 60 * 60), // 24 hours
                    ],
                ];
            }

            return [
                'success' => false,
                'message' => $data['statusMessage'] ?? 'Duitku V2 Inquiry failed',
            ];
        } catch (Exception $e) {
            logger()->error('Duitku createTransaction failed: '.$e->getMessage());

            return [
                'success' => false,
                'message' => $e->getMessage(),
            ];
        }
    }

    /**
     * Validate callback signature from Duitku Webhook using MD5
     * Signature formula: md5(merchantCode + amount + merchantOrderId + apiKey)
     */
    public function validateCallbackSignature(string $merchantCode, string $amount, string $merchantOrderId, string $receivedSignature): bool
    {
        $calculatedSignature = md5($merchantCode.$amount.$merchantOrderId.$this->apiKey);

        return hash_equals($calculatedSignature, $receivedSignature);
    }

    /**
     * Get detailed connection status for Duitku
     */
    public function getStatusDetails(): array
    {
        if (empty($this->merchantCode) || empty($this->apiKey)) {
            return [
                'success' => false,
                'message' => 'Merchant Code atau Merchant Key belum diisi',
            ];
        }

        try {
            $response = Api::getPaymentMethod(10000, $this->config);
            $data = json_decode($response, true);

            if (isset($data['error']) || isset($data['statusMessage'])) {
                return [
                    'success' => false,
                    'message' => $data['statusMessage'] ?? 'Unknown Duitku error',
                ];
            }

            return [
                'success' => true,
                'message' => 'Koneksi Berhasil',
            ];
        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => $e->getMessage(),
            ];
        }
    }
}
