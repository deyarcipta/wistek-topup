<?php

namespace App\Console\Commands;

use App\Models\Transaction;
use App\Services\DigiflazzService;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

#[Signature('digiflazz:check-pending')]
#[Description('Check pending or processing topup transactions from Digiflazz as a fallback for webhook failures')]
class CheckDigiflazzPendingTransactions extends Command
{
    /**
     * Execute the console command.
     */
    public function handle(DigiflazzService $digiflazz)
    {
        if (! $digiflazz->isConfigured()) {
            return;
        }

        // Get paid transactions where topup is still pending or processing created in the last 48 hours
        $transactions = Transaction::where('payment_status', 'paid')
            ->whereIn('topup_status', ['processing', 'pending'])
            ->where('created_at', '>=', now()->subHours(48))
            ->get();

        if ($transactions->isEmpty()) {
            return;
        }

        $this->info("Found {$transactions->count()} processing topup transactions to check.");

        foreach ($transactions as $transaction) {
            try {
                $res = $digiflazz->checkTopupStatus($transaction);
                $this->info("Invoice {$transaction->invoice}: Status => {$res['status']} | Note => {$res['note']}");
                Log::info("Digiflazz Polling Fallback for {$transaction->invoice}: Status => {$res['status']}");
            } catch (\Exception $e) {
                Log::error("Digiflazz Polling Fallback Error for {$transaction->invoice}: ".$e->getMessage());
            }
        }
    }
}
