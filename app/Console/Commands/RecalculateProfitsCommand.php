<?php

namespace App\Console\Commands;

use App\Models\Product;
use App\Models\Transaction;
use Illuminate\Console\Command;

class RecalculateProfitsCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'transactions:recalculate-profits';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Recalculate and update net profit for all past and existing transactions';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Recalculating net profit for all existing transactions...');

        $transactions = Transaction::all();
        $updatedCount = 0;

        foreach ($transactions as $transaction) {
            $product = Product::where('sku', $transaction->sku)->first();
            $costPrice = $product ? (float) $product->price_cost : 0;
            $grossPrice = (float) $transaction->price;

            $profit = max(0, $grossPrice - $costPrice);
            $transaction->profit = $profit;
            $transaction->saveQuietly();
            $updatedCount++;
        }

        $this->info("Successfully recalculated net profit for {$updatedCount} transactions!");

        return 0;
    }
}
