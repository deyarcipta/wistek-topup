<?php

namespace App\Services;

use App\Models\Setting;
use Exception;
use Illuminate\Support\Facades\Http;

class DigiflazzService
{
    protected $username;

    protected $apiKey;

    protected $webhookSecret;

    protected $baseUrl;

    public function __construct()
    {
        $this->username = Setting::get('digiflazz_username', env('DIGIFLAZZ_USERNAME'));
        $this->apiKey = Setting::get('digiflazz_api_key', env('DIGIFLAZZ_API_KEY'));
        $this->webhookSecret = Setting::get('digiflazz_webhook_secret', env('DIGIFLAZZ_WEBHOOK_SECRET'));
        $this->baseUrl = 'https://api.digiflazz.com/v1/';
    }

    /**
     * Check Digiflazz deposit balance
     */
    public function getBalance()
    {
        // sign = md5(username + apiKey + 'depo')
        $sign = md5($this->username.$this->apiKey.'depo');

        $payload = [
            'cmd' => 'depo',
            'username' => $this->username,
            'sign' => $sign,
        ];

        try {
            $response = Http::post($this->baseUrl.'cek-saldo', $payload);

            if ($response->successful()) {
                $data = $response->json();

                return $data['data']['deposit'] ?? 0;
            }

            return 0;
        } catch (Exception $e) {
            logger()->error('Digiflazz getBalance failed: '.$e->getMessage());

            return 0;
        }
    }

    /**
     * Get detailed connection status and balance for testing
     */
    public function getStatusDetails(): array
    {
        if (empty($this->username) || empty($this->apiKey)) {
            return [
                'success' => false,
                'message' => 'Username atau API Key belum diisi',
                'balance' => 0,
            ];
        }

        $sign = md5($this->username.$this->apiKey.'depo');

        $payload = [
            'cmd' => 'depo',
            'username' => $this->username,
            'sign' => $sign,
        ];

        try {
            $response = Http::post($this->baseUrl.'cek-saldo', $payload);

            if ($response->successful()) {
                $data = $response->json();

                if (isset($data['data']['rc']) && $data['data']['rc'] !== '00' && $data['data']['rc'] !== 0) {
                    return [
                        'success' => false,
                        'message' => $data['data']['message'] ?? 'Gagal',
                        'balance' => 0,
                    ];
                }

                if (isset($data['data']['deposit'])) {
                    return [
                        'success' => true,
                        'message' => 'Terhubung',
                        'balance' => $data['data']['deposit'],
                    ];
                }

                return [
                    'success' => false,
                    'message' => 'Format Respon Tidak Dikenali',
                    'balance' => 0,
                ];
            }

            $data = $response->json();
            if (isset($data['data']['message'])) {
                return [
                    'success' => false,
                    'message' => $data['data']['message'],
                    'balance' => 0,
                ];
            }

            return [
                'success' => false,
                'message' => 'HTTP Error '.$response->status(),
                'balance' => 0,
            ];
        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => $e->getMessage(),
                'balance' => 0,
            ];
        }
    }

    /**
     * Get price list of prepaid products
     */
    public function getProducts()
    {
        // sign = md5(username + apiKey + 'pricelist')
        $sign = md5($this->username.$this->apiKey.'pricelist');

        $payload = [
            'cmd' => 'prepaid',
            'username' => $this->username,
            'sign' => $sign,
        ];

        try {
            $response = Http::post($this->baseUrl.'price-list', $payload);

            if ($response->successful()) {
                $data = $response->json();

                return $data['data'] ?? [];
            }

            throw new Exception('Digiflazz Pricelist Error: '.$response->body());
        } catch (Exception $e) {
            logger()->error('Digiflazz getProducts failed: '.$e->getMessage());

            return [];
        }
    }

    /**
     * Order a topup product
     */
    public function orderTopup(string $refId, string $sku, string $targetNo)
    {
        // sign = md5(username + apiKey + refId)
        $sign = md5($this->username.$this->apiKey.$refId);

        $payload = [
            'username' => $this->username,
            'buyer_sku_code' => $sku,
            'customer_no' => $targetNo,
            'ref_id' => $refId,
            'sign' => $sign,
        ];

        logger()->info('Digiflazz Outgoing Order Request: username='.$this->username.', sku='.$sku.', customer_no='.$targetNo.', ref_id='.$refId);

        try {
            $response = Http::post($this->baseUrl.'transaction', $payload);

            if ($response->successful()) {
                $data = $response->json();
                if (isset($data['data'])) {
                    return [
                        'success' => true,
                        'data' => $data['data'],
                    ];
                }
            }

            return [
                'success' => false,
                'message' => $response->json()['data']['message'] ?? ($response->json()['message'] ?? 'Digiflazz API request failed'),
            ];
        } catch (Exception $e) {
            logger()->error('Digiflazz orderTopup failed: '.$e->getMessage());

            return [
                'success' => false,
                'message' => $e->getMessage(),
            ];
        }
    }

    /**
     * Validate Digiflazz callback webhook signature
     * Digiflazz webhook secret can be set in their developer dashboard.
     * They send an header containing signature: X-Digiflazz-Delivery-Signature: sha1=<signature_value>
     * Calculated as hash_hmac('sha1', rawBody, webhookSecret)
     */
    public function validateCallback(string $rawBody, string $receivedSignature)
    {
        if (empty($this->webhookSecret)) {
            // If secret is not configured, bypass check (not recommended for production)
            return true;
        }

        // Digiflazz signature typically starts with sha1=
        if (str_starts_with($receivedSignature, 'sha1=')) {
            $receivedSignature = substr($receivedSignature, 5);
        }

        $calculated = hash_hmac('sha1', $rawBody, $this->webhookSecret);

        return hash_equals($calculated, $receivedSignature);
    }
}
