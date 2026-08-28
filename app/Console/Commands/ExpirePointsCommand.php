<?php

namespace App\Console\Commands;

use App\Models\PointLog;
use App\Models\User;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;

#[Signature('points:expire')]
#[Description('Expire customer points older than 6 months')]
class ExpirePointsCommand extends Command
{
    /**
     * Execute the console command.
     */
    public function handle()
    {
        $expiredLogs = PointLog::where('amount', '>', 0)
            ->where('is_expired', false)
            ->where('expired_at', '<=', now())
            ->get()
            ->groupBy('user_id');

        $count = 0;
        foreach ($expiredLogs as $userId => $logs) {
            $user = User::find($userId);
            if (! $user) {
                continue;
            }

            $totalExpiredAmount = 0;
            foreach ($logs as $log) {
                $totalExpiredAmount += $log->amount;
                $log->update(['is_expired' => true]);
            }

            // Deduct the points from the user's balance (capped at current balance)
            $deductAmount = min($user->points_balance, $totalExpiredAmount);

            if ($deductAmount > 0) {
                $user->decrement('points_balance', $deductAmount);

                $user->pointLogs()->create([
                    'amount' => -$deductAmount,
                    'type' => 'expire',
                    'description' => 'Poin kedaluwarsa (masa berlaku 6 bulan habis)',
                    'is_expired' => true,
                ]);
                $count++;
            }
        }

        $this->info("Successfully expired points for {$count} users.");
    }
}
