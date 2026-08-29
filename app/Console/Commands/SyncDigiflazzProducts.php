<?php

namespace App\Console\Commands;

use App\Models\Product;
use App\Services\DigiflazzService;
use Illuminate\Console\Command;

class SyncDigiflazzProducts extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'products:sync-digiflazz';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Synchronize product price costs and statuses from Digiflazz API';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Starting Digiflazz product synchronization...');

        $digiflazz = new DigiflazzService;
        $dfProducts = $digiflazz->getProducts();

        if (empty($dfProducts)) {
            $this->error('Failed to retrieve products from Digiflazz API or empty list received.');

            return 1;
        }

        $this->info('Retrieved '.count($dfProducts).' products from Digiflazz.');

        $syncedCount = 0;
        $deactivatedCount = 0;
        $matchedProductIds = [];

        foreach ($dfProducts as $item) {
            $sku = $item['buyer_sku_code'] ?? null;
            if (! $sku) {
                continue;
            }

            // Find matching product in local database
            $product = Product::where('sku', $sku)->first();

            if ($product) {
                $matchedProductIds[] = $product->id;

                $priceCost = $item['price'] ?? $product->price_cost;
                // Determine active status considering possible string values from Digiflazz API
                $buyerActive = in_array(strtolower((string) ($item['buyer_product_status'] ?? '')), ['1', 'true', 'active'], true);
                $sellerActive = in_array(strtolower((string) ($item['seller_product_status'] ?? '')), ['1', 'true', 'active'], true);
                $isActive = $buyerActive && $sellerActive;
                $newStatus = $isActive ? 1 : 0;

                $statusChanged = $product->status !== $newStatus;
                $costChanged = (float) $product->price_cost !== (float) $priceCost;

                if ($statusChanged || $costChanged) {
                    $product->update([
                        'price_cost' => $priceCost,
                        'status' => $newStatus,
                    ]);

                    if ($statusChanged && $newStatus === 0) {
                        $deactivatedCount++;
                    }
                    $syncedCount++;
                }
            }
        }

        // Deactivate any local active products whose SKU was NOT returned in Digiflazz pricelist
        $unmatchedProducts = Product::where('status', 1)
            ->whereNotIn('id', $matchedProductIds)
            ->get();

        $unmatchedCount = 0;
        foreach ($unmatchedProducts as $unmatchedProduct) {
            $unmatchedProduct->update(['status' => 0]);
            $unmatchedCount++;
        }

        $this->info('Synchronization completed successfully!');
        $this->info("- Synced / Updated products: {$syncedCount}");
        $this->info("- Deactivated due to supplier disturbance: {$deactivatedCount}");
        $this->info("- Deactivated due to invalid/missing SKU on Digiflazz: {$unmatchedCount}");

        return 0;
    }
}
