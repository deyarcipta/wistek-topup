<?php

namespace App\Console\Commands;

use App\Http\Controllers\CallbackController;
use App\Models\Setting;
use App\Models\Transaction;
use App\Services\DigiflazzService;
use App\Services\DokuService;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

#[Signature('doku:check-pending')]
#[Description('Check pending transactions from DOKU as a fallback for webhook failures')]
class CheckDokuPendingTransactions extends Command
{
    /**
     * Execute the console command.
     */
    public function handle(DokuService $doku, CallbackController $callbackController, DigiflazzService $digiflazz)
    {
        $activeGateway = Setting::get('active_payment_gateway', 'duitku');
        if ($activeGateway !== 'doku') {
            return;
        }

        // Get unpaid transactions created in the last 24 hours
        $transactions = Transaction::where('payment_status', 'unpaid')
            ->where('created_at', '>=', now()->subHours(24))
            ->get();

        foreach ($transactions as $transaction) {
            $response = $doku->checkTransactionStatus($transaction->invoice);

            if ($response['success']) {
                $status = strtoupper($response['data']['order']['status'] ?? $response['data']['transaction']['status'] ?? '');

                if (in_array($status, ['SUCCESS', 'SUCCESSFUL', 'PAID', 'SETTLED'])) {
                    Log::info('DOKU Polling Fallback: Found PAID transaction: '.$transaction->invoice);
                    $reference = $response['data']['transaction']['id'] ?? $transaction->invoice;
                    $callbackController->fulfillPaidTransaction($transaction, $reference, $digiflazz);
                } elseif (in_array($status, ['FAILED', 'EXPIRED', 'CANCELLED'])) {
                    $transaction->payment_status = 'failed';
                    $transaction->topup_status = 'failed';
                    $transaction->note = 'Pembayaran dibatalkan/kedaluwarsa (DOKU Polling)';
                    $transaction->save();
                }
            }
        }
    }
}
