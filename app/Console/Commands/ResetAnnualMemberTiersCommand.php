<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;

class ResetAnnualMemberTiersCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'members:reset-annual-tiers';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Evaluate and reset member tier levels based on annual spending in the current calendar year';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $currentYear = now()->year;
        $this->info("Evaluating member tiers for calendar year {$currentYear}...");

        $members = User::where('role', 'member')->get();
        $evaluatedCount = 0;
        $upgradedCount = 0;
        $resetCount = 0;

        foreach ($members as $member) {
            $oldTier = $member->tier_level;
            $member->checkAndUpgradeTier();

            if ($oldTier !== $member->tier_level) {
                if ($member->tier_level === 'regular') {
                    $resetCount++;
                } else {
                    $upgradedCount++;
                }
            }

            $evaluatedCount++;
        }

        $this->info("Annual tier evaluation completed! Total: {$evaluatedCount}, Upgraded: {$upgradedCount}, Reset to Regular: {$resetCount}.");

        return 0;
    }
}
