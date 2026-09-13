<?php

namespace App\Console\Commands;

use App\Models\Product;
use App\Services\DigiflazzService;
use Illuminate\Console\Command;

class RecalculatePricesCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'products:recalculate-prices';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Recalculate price_sell for all products based on the latest tiered margin formula';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Recalculating price_sell, price_gold, price_platinum, and price_cash for all active products...');

        $products = Product::where('price_cost', '>', 0)->get();
        $updatedCount = 0;

        foreach ($products as $product) {
            $newPriceSell = DigiflazzService::calculatePriceSell((float) $product->price_cost);
            $newPriceGold = DigiflazzService::calculateGoldPrice((float) $product->price_cost);
            $newPricePlatinum = DigiflazzService::calculatePlatinumPrice((float) $product->price_cost);
            $newPriceCash = DigiflazzService::calculateFinalPriceCash((float) $product->price_cost);

            $product->update([
                'price_sell' => $newPriceSell,
                'price_gold' => $newPriceGold,
                'price_platinum' => $newPricePlatinum,
                'price_cash' => $newPriceCash,
            ]);
            $updatedCount++;
        }

        $this->info("Successfully recalculated and updated Regular, Gold (VIP), Platinum (VVIP), & Cash prices for {$updatedCount} products!");

        return 0;
    }
}
