<?php

namespace App\Services;

use App\Models\Category;
use App\Models\Product;
use App\Models\Setting;
use Exception;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;

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
    public function getProducts(bool $forceRefresh = false)
    {
        $cacheKey = 'digiflazz_pricelist_'.$this->username;
        if (! $forceRefresh && Cache::has($cacheKey)) {
            return Cache::get($cacheKey);
        }

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
                $rawProducts = $data['data'] ?? [];

                // Check if Digiflazz returned a rate limit or error object instead of a list
                if (isset($rawProducts['rc'])) {
                    $msg = $rawProducts['message'] ?? ('Digiflazz error rc: '.$rawProducts['rc']);
                    throw new Exception($msg);
                }

                // If not empty, verify it is a list of product items
                if (! empty($rawProducts) && ! is_array($rawProducts)) {
                    throw new Exception('Format respon produk Digiflazz tidak valid.');
                }

                if (! empty($rawProducts) && isset($rawProducts[0]) && is_array($rawProducts[0])) {
                    // Cache valid product list for 2 minutes to prevent rate limit RC 83
                    Cache::put($cacheKey, $rawProducts, now()->addMinutes(2));
                }

                return $rawProducts;
            }

            throw new Exception('Digiflazz Pricelist Error: '.$response->body());
        } catch (Exception $e) {
            logger()->error('Digiflazz getProducts failed: '.$e->getMessage());

            throw $e;
        }
    }

    /**
     * Synchronize products from Digiflazz into local database
     *
     * @return array{total: int, created: int, updated: int, deactivated: int}
     */
    public function syncProducts(bool $forceRefresh = false): array
    {
        $dfProducts = $this->getProducts($forceRefresh);

        if (empty($dfProducts)) {
            return [
                'total' => 0,
                'created' => 0,
                'updated' => 0,
                'deactivated' => 0,
            ];
        }

        $createdCount = 0;
        $updatedCount = 0;
        $deactivatedCount = 0;
        $matchedProductIds = [];

        // 1. Group Digiflazz items by brand + product_name to select active seller with lowest cost
        $bestSellersMap = [];
        foreach ($dfProducts as $item) {
            $sku = trim((string) ($item['buyer_sku_code'] ?? ''));
            if ($sku === '') {
                continue;
            }

            $priceCost = (float) ($item['price'] ?? 0);
            $buyerActive = in_array(strtolower((string) ($item['buyer_product_status'] ?? '')), ['1', 'true', 'active'], true);
            $sellerActive = in_array(strtolower((string) ($item['seller_product_status'] ?? '')), ['1', 'true', 'active'], true);
            $isDigiflazzActive = $buyerActive && $sellerActive;

            $brand = trim((string) ($item['brand'] ?? ''));
            $productName = trim((string) ($item['product_name'] ?? $sku));
            $groupKey = Str::slug(($brand !== '' ? $brand.' ' : '').$productName);

            if (! isset($bestSellersMap[$groupKey])) {
                $bestSellersMap[$groupKey] = [
                    'sku' => $sku,
                    'price_cost' => $priceCost,
                    'is_active' => $isDigiflazzActive,
                    'item' => $item,
                ];
            } else {
                $existing = $bestSellersMap[$groupKey];
                $shouldReplace = false;

                if ($isDigiflazzActive) {
                    if (! $existing['is_active']) {
                        $shouldReplace = true;
                    } elseif ($priceCost < $existing['price_cost']) {
                        $shouldReplace = true;
                    }
                }

                if ($shouldReplace) {
                    $bestSellersMap[$groupKey] = [
                        'sku' => $sku,
                        'price_cost' => $priceCost,
                        'is_active' => $isDigiflazzActive,
                        'item' => $item,
                    ];
                }
            }
        }

        // 2. Process each item, auto-linking products to the cheapest active seller SKU
        foreach ($dfProducts as $item) {
            $sku = trim((string) ($item['buyer_sku_code'] ?? ''));
            if ($sku === '') {
                continue;
            }

            $priceCost = (float) ($item['price'] ?? 0);
            $buyerActive = in_array(strtolower((string) ($item['buyer_product_status'] ?? '')), ['1', 'true', 'active'], true);
            $sellerActive = in_array(strtolower((string) ($item['seller_product_status'] ?? '')), ['1', 'true', 'active'], true);
            $isDigiflazzActive = $buyerActive && $sellerActive;

            $brand = trim((string) ($item['brand'] ?? ''));
            $productName = trim((string) ($item['product_name'] ?? $sku));
            $groupKey = Str::slug(($brand !== '' ? $brand.' ' : '').$productName);

            $bestSeller = $bestSellersMap[$groupKey] ?? [
                'sku' => $sku,
                'price_cost' => $priceCost,
                'is_active' => $isDigiflazzActive,
                'item' => $item,
            ];

            // Match product by exact SKU or by Name & Category
            $product = Product::where('sku', $sku)->first();
            if (! $product) {
                $product = Product::where('name', $productName)->first();
            }

            if ($product) {
                $matchedProductIds[] = $product->id;

                // Auto-switch to the cheapest active seller SKU if available
                $targetSku = $bestSeller['sku'];
                $targetCost = $bestSeller['price_cost'];
                $targetStatus = $bestSeller['is_active'];

                $skuChanged = $product->sku !== $targetSku;
                $costChanged = (float) $product->price_cost !== $targetCost;
                $dfStatusChanged = (bool) $product->digiflazz_status !== $targetStatus;

                if ($skuChanged || $costChanged || $dfStatusChanged) {
                    $product->update([
                        'sku' => $targetSku,
                        'price_cost' => $targetCost,
                        'digiflazz_status' => $targetStatus,
                    ]);

                    if ($dfStatusChanged && ! $targetStatus) {
                        $deactivatedCount++;
                    }
                    $updatedCount++;
                }
            } else {
                // Auto-import product using cheapest active seller SKU
                if ($buyerActive) {
                    $categoryName = trim((string) ($item['category'] ?? ''));

                    $category = null;
                    if ($brand !== '') {
                        $brandSlug = Str::slug($brand);
                        $category = Category::where('slug', $brandSlug)->first();

                        if (! $category) {
                            $category = Category::where('name', 'like', "%{$brand}%")->first();
                        }

                        $type = self::determineCategoryType($categoryName, $brand);

                        if (! $category) {
                            $category = Category::create([
                                'name' => ucwords(strtolower($brand)),
                                'slug' => $brandSlug ?: Str::slug($productName),
                                'type' => $type,
                                'status' => true,
                            ]);
                        } elseif ($category->type === 'game' && $type !== 'game') {
                            $category->update(['type' => $type]);
                        }
                    }

                    if (! $category) {
                        $category = Category::first();
                    }

                    $optimalSku = $bestSeller['sku'];
                    $optimalCost = $bestSeller['price_cost'];
                    $optimalStatus = $bestSeller['is_active'];

                    $margin = match (true) {
                        $optimalCost <= 5000 => 500,
                        $optimalCost <= 20000 => 1000,
                        $optimalCost <= 50000 => 2000,
                        $optimalCost <= 100000 => 3500,
                        default => ceil(($optimalCost * 0.05) / 100) * 100,
                    };
                    $priceSell = $optimalCost + $margin;

                    if ($category) {
                        $newProduct = Product::create([
                            'category_id' => $category->id,
                            'name' => $productName,
                            'sku' => $optimalSku,
                            'price_cost' => $optimalCost,
                            'price_sell' => $priceSell,
                            'status' => true,
                            'digiflazz_status' => $optimalStatus,
                        ]);

                        $matchedProductIds[] = $newProduct->id;
                        $createdCount++;
                    }
                }
            }
        }

        // Mark any existing products that were not returned by Digiflazz as inactive in Digiflazz
        $unmatchedProducts = Product::where('digiflazz_status', true)
            ->whereNotIn('id', array_unique($matchedProductIds))
            ->get();

        foreach ($unmatchedProducts as $unmatchedProduct) {
            $unmatchedProduct->update(['digiflazz_status' => false]);
            $deactivatedCount++;
        }

        return [
            'total' => count($dfProducts),
            'created' => $createdCount,
            'updated' => $updatedCount,
            'deactivated' => $deactivatedCount,
        ];
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

            logger()->info('Digiflazz Raw Response: Status='.$response->status().', Body='.$response->body());

            if ($response->successful()) {
                $data = $response->json();
                if (isset($data['data'])) {
                    return [
                        'success' => true,
                        'data' => $data['data'],
                    ];
                }
            }

            // Handle error responses, including product inactive (rc 43)
            $errorData = $response->json();
            $message = $errorData['data']['message'] ?? ($errorData['message'] ?? 'Digiflazz API request failed');
            $rc = $errorData['data']['rc'] ?? null;
            if ($rc === '43') {
                $message = 'Produk tidak aktif atau sedang gangguan di Digiflazz.';
            }

            return [
                'success' => false,
                'message' => $message,
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

    /**
     * Determine category type intelligently from Digiflazz category & brand names
     */
    public static function determineCategoryType(string $categoryName, string $brandName = ''): string
    {
        $cat = strtolower(trim($categoryName));
        $brand = strtolower(trim($brandName));

        // 1. PLN / Token Listrik
        if (str_contains($cat, 'pln') || str_contains($cat, 'listrik') || str_contains($brand, 'pln') || str_contains($brand, 'listrik')) {
            return 'pln';
        }

        // 2. Streaming & Hiburan (Vidio, Netflix, Spotify, K-Vision, GOL, WeTV, Viu, Disney+, YouTube, dll.)
        $streamingBrands = ['netflix', 'vidio', 'spotify', 'wetv', 'viu', 'disney', 'youtube', 'k-vision', 'kvision', 'gol', 'mola', 'iqiyi', 'catchplay', 'genflix', 'vision+'];
        if (
            str_contains($cat, 'streaming') || str_contains($cat, 'hiburan') || str_contains($cat, 'tv') ||
            in_array($brand, $streamingBrands)
        ) {
            return 'streaming';
        }
        foreach ($streamingBrands as $sb) {
            if (str_contains($brand, $sb)) {
                return 'streaming';
            }
        }

        // 3. Pertamina Gas, PDAM, BPJS, PPOB, Tagihan
        if (
            str_contains($cat, 'gas') || str_contains($brand, 'gas') ||
            str_contains($cat, 'pdam') || str_contains($brand, 'pdam') ||
            str_contains($cat, 'bpjs') || str_contains($brand, 'bpjs') ||
            str_contains($brand, 'indihome') ||
            str_contains($cat, 'pascabayar') || str_contains($cat, 'tagihan')
        ) {
            return 'tagihan';
        }

        // 3. E-Money / E-Wallet
        $emoneyBrands = ['dana', 'gopay', 'go-pay', 'go pay', 'ovo', 'shopeepay', 'shopee pay', 'shopee-pay', 'linkaja', 'link aja', 'maxim', 'doku', 'isaku', 'i-saku', 'sakuku'];
        if (
            str_contains($cat, 'emoney') || str_contains($cat, 'e-money') || str_contains($cat, 'ewallet') || str_contains($cat, 'e-wallet') ||
            str_contains($cat, 'wallet') ||
            in_array($brand, $emoneyBrands)
        ) {
            return 'emoney';
        }
        foreach ($emoneyBrands as $eb) {
            if (str_contains($brand, $eb)) {
                return 'emoney';
            }
        }

        // 4. Pulsa & Paket Data
        $telcoBrands = ['telkomsel', 'xl', 'axis', 'indosat', 'tri', 'three', 'smartfren', 'smart', 'by.u', 'byu'];
        if (
            str_contains($cat, 'pulsa') || str_contains($cat, 'data') || str_contains($cat, 'paket') || str_contains($cat, 'internet') ||
            str_contains($cat, 'sms') || str_contains($cat, 'masa aktif') ||
            in_array($brand, $telcoBrands)
        ) {
            return 'pulsa';
        }
        foreach ($telcoBrands as $tb) {
            if (str_contains($brand, $tb)) {
                return 'pulsa';
            }
        }

        // 5. Voucher
        if (str_contains($cat, 'voucher') || str_contains($brand, 'voucher')) {
            return 'voucher';
        }

        // 6. Game (default)
        return 'game';
    }

    /**
     * Inquire account name using Digiflazz Inquiry SKU (e.g. pre33614125 for Mobile Legends Cek Username)
     */
    public function inquireAccountName(string $inquirySku, string $customerNo): array
    {
        if (empty($inquirySku) || empty($customerNo)) {
            return [
                'success' => false,
                'message' => 'SKU Inquiry dan Nomor Pelanggan/Target wajib diisi.',
            ];
        }

        $refId = 'INQ-'.date('YmdHis').rand(100, 999);
        $sign = md5($this->username.$this->apiKey.$refId);

        $payload = [
            'username' => $this->username,
            'buyer_sku_code' => $inquirySku,
            'customer_no' => $customerNo,
            'ref_id' => $refId,
            'sign' => $sign,
        ];

        try {
            $response = Http::post($this->baseUrl.'transaction', $payload);

            if ($response->successful()) {
                $data = $response->json();
                if (isset($data['data'])) {
                    $item = $data['data'];
                    $customerName = trim((string) ($item['customer_name'] ?? ''));

                    if ($customerName === '' && ! empty($item['sn'])) {
                        $parts = explode('/', $item['sn']);
                        $customerName = trim($parts[0]);
                    }

                    if ($customerName !== '') {
                        return [
                            'success' => true,
                            'nickname' => $customerName,
                            'message' => $item['message'] ?? 'Berhasil verifikasi via Digiflazz',
                        ];
                    }

                    return [
                        'success' => false,
                        'message' => $item['message'] ?? 'Data akun tidak ditemukan.',
                    ];
                }
            }

            return [
                'success' => false,
                'message' => 'Gagal melakukan inquiry via Digiflazz.',
            ];
        } catch (Exception $e) {
            logger()->error('Digiflazz inquireAccountName failed: '.$e->getMessage());

            return [
                'success' => false,
                'message' => $e->getMessage(),
            ];
        }
    }
}
