<?php

namespace App\Services;

use App\Filament\Pages\ManagePriceMarginSettings;
use App\Models\Category;
use App\Models\Product;
use App\Models\Setting;
use App\Models\Transaction;
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
     * Check if Digiflazz credentials are set and configured
     */
    public function isConfigured(): bool
    {
        if (empty($this->username) || empty($this->apiKey)) {
            return false;
        }

        if ($this->username === 'YOUR_DIGIFLAZZ_USERNAME' || $this->apiKey === 'YOUR_DIGIFLAZZ_API_KEY') {
            return false;
        }

        return true;
    }

    /**
     * Check whether Digiflazz deposit balance is sufficient for purchasing a product
     *
     * @return array{sufficient: bool, balance: float, cost: float, message: string|null}
     */
    public function checkBalanceForProduct(Product $product): array
    {
        if (! $this->isConfigured()) {
            return [
                'sufficient' => true,
                'balance' => 0,
                'cost' => (float) $product->price_cost,
                'message' => null,
            ];
        }

        $status = $this->getStatusDetails();

        if (! $status['success']) {
            return [
                'sufficient' => true,
                'balance' => 0,
                'cost' => (float) $product->price_cost,
                'message' => null,
            ];
        }

        $balance = (float) $status['balance'];
        $cost = (float) $product->price_cost;

        if ($cost > 0 && $balance < $cost) {
            $formattedBalance = 'Rp '.number_format($balance, 0, ',', '.');
            $formattedCost = 'Rp '.number_format($cost, 0, ',', '.');

            return [
                'sufficient' => false,
                'balance' => $balance,
                'cost' => $cost,
                'message' => "Saldo deposit Digiflazz saat ini ({$formattedBalance}) tidak mencukupi untuk memproses nominal produk \"{$product->name}\" (Harga Modal: {$formattedCost}).",
            ];
        }

        return [
            'sufficient' => true,
            'balance' => $balance,
            'cost' => $cost,
            'message' => null,
        ];
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

        $isTrustedEnabled = Setting::get('digiflazz_trusted_seller_enabled', '1') === '1';
        $priceTolerance = (float) Setting::get('digiflazz_price_tolerance', '200');

        // 1. Group Digiflazz items by brand + product_name to select active & trusted seller
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

            // Compute Trust Score for seller evaluation
            $desc = strtolower(($item['desc'] ?? '').' '.($item['seller_name'] ?? '').' '.($item['note'] ?? ''));
            $trustScore = 100;
            if (str_contains($desc, 'instant') || str_contains($desc, '24 jam') || str_contains($desc, '24h') || str_contains($desc, 'otmatis') || str_contains($desc, 'official') || str_contains($desc, 'gas')) {
                $trustScore += 20;
            }
            if (str_contains($desc, 'slow') || str_contains($desc, 'manual') || str_contains($desc, '1x24') || str_contains($desc, 'proses lama') || str_contains($desc, 'antri')) {
                $trustScore -= 30;
            }

            if (! isset($bestSellersMap[$groupKey])) {
                $bestSellersMap[$groupKey] = [
                    'sku' => $sku,
                    'price_cost' => $priceCost,
                    'is_active' => $isDigiflazzActive,
                    'trust_score' => $trustScore,
                    'item' => $item,
                ];
            } else {
                $existing = $bestSellersMap[$groupKey];
                $shouldReplace = false;

                if ($isDigiflazzActive) {
                    if (! $existing['is_active']) {
                        $shouldReplace = true;
                    } else {
                        $priceDiff = $priceCost - $existing['price_cost'];

                        if ($isTrustedEnabled && abs($priceDiff) <= $priceTolerance) {
                            // Within price tolerance: favor seller with higher Trust Score
                            if ($trustScore > $existing['trust_score']) {
                                $shouldReplace = true;
                            } elseif ($trustScore === $existing['trust_score'] && $priceCost < $existing['price_cost']) {
                                $shouldReplace = true;
                            }
                        } elseif ($priceCost < $existing['price_cost']) {
                            $shouldReplace = true;
                        }
                    }
                }

                if ($shouldReplace) {
                    $bestSellersMap[$groupKey] = [
                        'sku' => $sku,
                        'price_cost' => $priceCost,
                        'is_active' => $isDigiflazzActive,
                        'trust_score' => $trustScore,
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
                    $updateData = [
                        'sku' => $targetSku,
                        'price_cost' => $targetCost,
                        'digiflazz_status' => $targetStatus,
                    ];

                    if ($costChanged) {
                        $updateData['price_sell'] = self::calculatePriceSell($targetCost);
                    }

                    $product->update($updateData);

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

                    $margin = self::calculateMargin($optimalCost);
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
     * Find candidate alternative sellers in Digiflazz pricelist for a product.
     * Enforces Margin Safety Guard: Only returns sellers where candidate_cost <= product->price_cost.
     *
     * @return array<int, array{sku: string, seller_name: string, price_cost: float, product_name: string}>
     */
    public function findAlternativeSellerSkus(Product $product, array $failedSkus = []): array
    {
        try {
            $dfProducts = $this->getProducts(false);
        } catch (Exception $e) {
            logger()->error('findAlternativeSellerSkus failed to load pricelist: '.$e->getMessage());

            return [];
        }

        if (empty($dfProducts)) {
            return [];
        }

        $categoryName = $product->category ? $product->category->name : '';
        $brandSlug = $product->category ? Str::slug($product->category->slug) : '';

        $primaryCost = (float) $product->price_cost;
        if ($primaryCost <= 0) {
            $primaryCost = (float) $product->price_sell;
        }

        $candidates = [];

        foreach ($dfProducts as $item) {
            $sku = trim((string) ($item['buyer_sku_code'] ?? ''));
            if ($sku === '' || in_array($sku, $failedSkus, true) || $sku === $product->sku) {
                continue;
            }

            // Check if seller status is active
            $buyerActive = in_array(strtolower((string) ($item['buyer_product_status'] ?? '')), ['1', 'true', 'active'], true);
            $sellerActive = in_array(strtolower((string) ($item['seller_product_status'] ?? '')), ['1', 'true', 'active'], true);

            if (! $buyerActive || ! $sellerActive) {
                continue;
            }

            $candidateCost = (float) ($item['price'] ?? 0);

            // 1. MARGIN SAFETY GUARD: candidate cost MUST be <= primary product cost (or price_sell if cost 0)
            if ($primaryCost > 0 && $candidateCost > $primaryCost) {
                continue;
            }

            $itemBrand = trim((string) ($item['brand'] ?? ''));
            $itemProductName = trim((string) ($item['product_name'] ?? ''));

            // Check if brand matches
            $brandMatches = false;
            if ($brandSlug !== '' && $itemBrand !== '') {
                $brandMatches = str_contains(Str::slug($itemBrand), $brandSlug) || str_contains($brandSlug, Str::slug($itemBrand));
            } elseif ($categoryName !== '' && $itemBrand !== '') {
                $brandMatches = str_contains(strtolower($itemBrand), strtolower($categoryName)) || str_contains(strtolower($categoryName), strtolower($itemBrand));
            }

            if (! $brandMatches && $categoryName !== '') {
                $itemCat = trim((string) ($item['category'] ?? ''));
                $brandMatches = str_contains(strtolower($itemCat), strtolower($categoryName));
            }

            if (! $brandMatches) {
                continue;
            }

            // Check product nominal/name similarity
            $cleanProductName = strtolower(trim(preg_replace('/[^a-zA-Z0-9]/', ' ', $product->name)));
            $cleanItemName = strtolower(trim(preg_replace('/[^a-zA-Z0-9]/', ' ', $itemProductName)));

            preg_match_all('/\d+/', $cleanProductName, $prodNums);
            preg_match_all('/\d+/', $cleanItemName, $itemNums);

            $prodNumStr = implode('-', $prodNums[0] ?? []);
            $itemNumStr = implode('-', $itemNums[0] ?? []);

            $nameMatches = false;
            if ($cleanProductName === $cleanItemName) {
                $nameMatches = true;
            } elseif ($prodNumStr !== '' && $prodNumStr === $itemNumStr) {
                $nameMatches = true;
            } elseif (str_contains($cleanItemName, $cleanProductName) || str_contains($cleanProductName, $cleanItemName)) {
                $nameMatches = true;
            }

            if ($nameMatches) {
                $candidates[] = [
                    'sku' => $sku,
                    'seller_name' => trim((string) ($item['seller_name'] ?? 'Digiflazz Seller')),
                    'price_cost' => $candidateCost,
                    'product_name' => $itemProductName,
                ];
            }
        }

        // Sort candidates by price_cost ASC (cheapest candidate first)
        usort($candidates, fn ($a, $b) => $a['price_cost'] <=> $b['price_cost']);

        return $candidates;
    }

    /**
     * Order topup with automatic multi-seller failover protection.
     * If primary seller SKU fails or is inactive/gangguan, it attempts up to 3 candidate sellers
     * with candidate_cost <= primary_cost, ensuring 0 loss margin safety.
     *
     * @return array{success: bool, data?: array, message?: string}
     */
    public function orderTopupWithFailover(Transaction $transaction, ?Product $product = null): array
    {
        if (! $product) {
            $product = Product::where('sku', $transaction->sku)->first();
            if (! $product && $transaction->product_name) {
                $product = Product::where('name', $transaction->product_name)->first();
            }
        }

        $targetNo = str_replace([' ', '(', ')', '-'], '', $transaction->target_no);
        $primarySku = $transaction->sku;

        // Check deposit balance first
        if ($product && $this->isConfigured()) {
            $balanceCheck = $this->checkBalanceForProduct($product);
            if (! $balanceCheck['sufficient']) {
                return [
                    'success' => false,
                    'message' => $balanceCheck['message'] ?? 'Saldo Digiflazz tidak mencukupi.',
                ];
            }
        }

        logger()->info("Attempting primary Digiflazz order: ref_id={$transaction->invoice}, sku={$primarySku}");
        $primaryResponse = $this->orderTopup($transaction->invoice, $primarySku, $targetNo);

        if ($primaryResponse['success']) {
            $status = strtolower($primaryResponse['data']['status'] ?? '');
            if ($status !== 'gagal') {
                return $primaryResponse;
            }
        }

        // Primary SKU failed or returned error status (e.g. RC 43 / Gagal / Offline)
        $failedSkus = [$primarySku];
        $failReason = $primaryResponse['message'] ?? ($primaryResponse['data']['message'] ?? 'Primary seller failed');
        logger()->warning("Primary seller SKU {$primarySku} failed for invoice {$transaction->invoice}: {$failReason}. Initiating Auto-Failover...");

        if ($product) {
            $candidates = $this->findAlternativeSellerSkus($product, $failedSkus);

            $attempt = 1;
            foreach ($candidates as $candidate) {
                if ($attempt > 3) {
                    break; // Maximum 3 failover retries
                }

                $candidateSku = $candidate['sku'];
                $failoverRefId = "{$transaction->invoice}-F{$attempt}";

                logger()->info("Auto-Failover Attempt #{$attempt} for invoice {$transaction->invoice}: trying candidate SKU {$candidateSku} (Seller: {$candidate['seller_name']}, Cost: Rp {$candidate['price_cost']})");

                $retryResponse = $this->orderTopup($failoverRefId, $candidateSku, $targetNo);

                if ($retryResponse['success']) {
                    $retryStatus = strtolower($retryResponse['data']['status'] ?? '');
                    if ($retryStatus !== 'gagal') {
                        logger()->info("Auto-Failover SUCCESS on attempt #{$attempt} using candidate SKU {$candidateSku} for invoice {$transaction->invoice}!");

                        // Update transaction record with the new working SKU & note
                        $transaction->sku = $candidateSku;
                        $transaction->note = "Sukses via Failover Seller ({$candidate['seller_name']} - SKU {$candidateSku})";
                        $transaction->save();

                        return $retryResponse;
                    }
                }

                $failedSkus[] = $candidateSku;
                $attempt++;
            }
        }

        return $primaryResponse;
    }

    /**
     * Check transaction status directly from Digiflazz API and sync DB status
     *
     * @return array{success: bool, status: string, note: string|null, message: string|null}
     */
    public function checkTopupStatus(Transaction $transaction): array
    {
        if (! $this->isConfigured()) {
            return [
                'success' => false,
                'status' => $transaction->topup_status,
                'note' => $transaction->note,
                'message' => 'Digiflazz belum terkonfigurasi.',
            ];
        }

        $targetNo = str_replace([' ', '(', ')', '-'], '', $transaction->target_no);
        $sign = md5($this->username.$this->apiKey.$transaction->invoice);

        $payload = [
            'username' => $this->username,
            'buyer_sku_code' => $transaction->sku,
            'customer_no' => $targetNo,
            'ref_id' => $transaction->invoice,
            'sign' => $sign,
        ];

        logger()->info('Digiflazz checkTopupStatus request for ref_id='.$transaction->invoice);

        try {
            $response = Http::post($this->baseUrl.'transaction', $payload);

            logger()->info('Digiflazz checkTopupStatus response: Status='.$response->status().' Body='.$response->body());

            if ($response->successful()) {
                $body = $response->json();
                $data = $body['data'] ?? [];

                if (! empty($data)) {
                    $rawStatus = strtolower($data['status'] ?? '');
                    $sn = $data['sn'] ?? '';
                    $message = $data['message'] ?? '';

                    $statusBefore = $transaction->topup_status;

                    if ($rawStatus === 'sukses') {
                        $transaction->topup_status = 'success';
                        $transaction->note = $sn ?: 'Sukses';
                        $transaction->save();
                        $transaction->creditPointsIfEligible();

                        if ($statusBefore !== 'success' && $transaction->customer_phone) {
                            try {
                                $whatsapp = new WhatsappService;
                                $whatsapp->sendMessage(
                                    $transaction->customer_phone,
                                    "Top-up BERHASIL dikirim! 🎉\n\n*Invoice*: {$transaction->invoice}\n*Produk*: {$transaction->category_name} - {$transaction->product_name}\n*Target*: {$transaction->target_no}\n*Serial Number (SN)*: {$transaction->note}\n\nTerima kasih telah berbelanja di Wistek Topup!"
                                );
                            } catch (\Throwable $ex) {
                                logger()->error('WhatsApp topup success notification failed: '.$ex->getMessage());
                            }
                        }

                        return [
                            'success' => true,
                            'status' => 'success',
                            'note' => $transaction->note,
                            'message' => 'Top-up Berhasil',
                        ];
                    } elseif ($rawStatus === 'gagal') {
                        $transaction->topup_status = 'failed';
                        $transaction->note = $message ?: 'Ditolak oleh provider';
                        $transaction->save();

                        if ($statusBefore !== 'failed' && $transaction->customer_phone) {
                            try {
                                $whatsapp = new WhatsappService;
                                $whatsapp->sendMessage(
                                    $transaction->customer_phone,
                                    "Mohon maaf, transaksi top-up untuk Invoice *{$transaction->invoice}* GAGAL diproses oleh provider.\nDetail: {$transaction->note}\n\nSilakan hubungi Customer Service kami untuk bantuan pengembalian dana."
                                );
                            } catch (\Throwable $ex) {
                                logger()->error('WhatsApp topup failed notification failed: '.$ex->getMessage());
                            }
                        }

                        return [
                            'success' => true,
                            'status' => 'failed',
                            'note' => $transaction->note,
                            'message' => 'Top-up Gagal',
                        ];
                    } else {
                        $transaction->topup_status = 'processing';
                        if (empty($transaction->note)) {
                            $transaction->note = 'Sedang diproses oleh provider';
                            $transaction->save();
                        }

                        return [
                            'success' => true,
                            'status' => 'processing',
                            'note' => $transaction->note,
                            'message' => 'Masih Diproses oleh Provider',
                        ];
                    }
                }
            }

            return [
                'success' => false,
                'status' => $transaction->topup_status,
                'note' => $transaction->note,
                'message' => 'Gagal terhubung ke API Digiflazz',
            ];
        } catch (Exception $e) {
            logger()->error('Digiflazz checkTopupStatus failed for '.$transaction->invoice.': '.$e->getMessage());

            return [
                'success' => false,
                'status' => $transaction->topup_status,
                'note' => $transaction->note,
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

    public static function calculateMargin(float $cost): float
    {
        $rules = ManagePriceMarginSettings::getTierRules();
        usort($rules, fn ($a, $b) => ((float) ($a['max_amount'] ?? 0) ?: 999999999) <=> ((float) ($b['max_amount'] ?? 0) ?: 999999999));

        foreach ($rules as $rule) {
            $max = (float) ($rule['max_amount'] ?? 0);
            if ($max > 0 && $cost <= $max) {
                return (float) ($rule['margin_online'] ?? 0);
            }
        }

        $defaultRule = end($rules);
        $val = (float) ($defaultRule['margin_online'] ?? 3.5);
        if ($val <= 100) {
            return ceil(($cost * ($val / 100)) / 100) * 100;
        }

        return $val;
    }

    public static function calculateCashMargin(float $cost): float
    {
        $rules = ManagePriceMarginSettings::getTierRules();
        usort($rules, fn ($a, $b) => ((float) ($a['max_amount'] ?? 0) ?: 999999999) <=> ((float) ($b['max_amount'] ?? 0) ?: 999999999));

        foreach ($rules as $rule) {
            $max = (float) ($rule['max_amount'] ?? 0);
            if ($max > 0 && $cost <= $max) {
                return (float) ($rule['margin_cash'] ?? 0);
            }
        }

        $defaultRule = end($rules);
        $val = (float) ($defaultRule['margin_cash'] ?? 2.5);
        if ($val <= 100) {
            return ceil(($cost * ($val / 100)) / 100) * 100;
        }

        return $val;
    }

    public static function roundCashPrice(float $amount): float
    {
        $thousand = floor($amount / 1000) * 1000;
        $remainder = $amount - $thousand;

        if ($remainder < 250) {
            return $thousand;
        } elseif ($remainder < 650) {
            return $thousand + 500;
        } else {
            return $thousand + 1000;
        }
    }

    public static function calculatePriceSell(float $cost): float
    {
        return $cost + self::calculateMargin($cost);
    }

    public static function calculateFinalPriceCash(float $cost): float
    {
        $rawCash = $cost + self::calculateCashMargin($cost);

        return self::roundCashPrice($rawCash);
    }
}
