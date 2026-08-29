<?php

namespace App\Console\Commands;

use App\Services\DigiflazzService;
use Illuminate\Console\Command;

class SyncDigiflazzProducts extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'products:sync-digiflazz {--force : Force refresh bypass cache}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Synchronize product price costs, import active products, and update statuses from Digiflazz API';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Starting Digiflazz product synchronization...');

        $digiflazz = new DigiflazzService;

        try {
            $force = $this->option('force') ?? false;
            $result = $digiflazz->syncProducts($force);

            if ($result['total'] === 0) {
                $this->warn('No products retrieved from Digiflazz or pricelist was empty.');

                return 0;
            }

            $this->info('Synchronization completed successfully!');
            $this->info("- Total Digiflazz products processed: {$result['total']}");
            $this->info("- Newly imported active products: {$result['created']}");
            $this->info("- Updated prices & statuses: {$result['updated']}");
            $this->info("- Marked inactive/gangguan: {$result['deactivated']}");

            return 0;
        } catch (\Exception $e) {
            $this->error('Digiflazz Sync Failed: '.$e->getMessage());

            return 1;
        }
    }
}
