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

        foreach ($dfProducts as $item) {
            $sku = $item['buyer_sku_code'] ?? null;
            if (! $sku) {
                continue;
            }

            // Find matching product in local database
            $product = Product::where('sku', $sku)->first();

            if ($product) {
                $priceCost = $item['price'] ?? $product->price_cost;
                $isActive = ($item['buyer_product_status'] ?? false) && ($item['seller_product_status'] ?? false);
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

        $this->info('Synchronization completed successfully!');
        $this->info("- Synced / Updated products: {$syncedCount}");
        $this->info("- Deactivated due to supplier disturbance: {$deactivatedCount}");

        return 0;
    }
}
