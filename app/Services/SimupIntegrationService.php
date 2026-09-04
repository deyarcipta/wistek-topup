<?php

namespace App\Services;

use App\Models\Setting;
use App\Models\Transaction;
use Illuminate\Support\Facades\Http;

class SimupIntegrationService
{
    /**
     * Sync single transaction to simup_wistek server
     */
    public function syncTransaction(Transaction $transaction): bool
    {
        if ($transaction->payment_status !== 'paid') {
            return false;
        }

        if ($transaction->is_synced_to_simup) {
            return true;
        }

        $enabled = Setting::get('simup_enabled', '1') === '1';
        if (! $enabled) {
            return false;
        }

        $simupUrl = Setting::get('simup_webhook_url', config('services.simup.url'));
        $simupSecret = Setting::get('simup_webhook_secret', config('services.simup.secret'));

        if (empty($simupUrl) || empty($simupSecret)) {
            logger()->warning('Simup integration skipped: SIMUP URL or Secret is not configured.');

            return false;
        }

        $endpoint = rtrim($simupUrl, '/').'/api/v1/webhook/topup-income';

        try {
            $response = Http::withHeaders([
                'X-Wistek-Secret' => $simupSecret,
                'Accept' => 'application/json',
            ])->timeout(10)->post($endpoint, [
                'kode_transaksi' => $transaction->invoice,
                'tanggal' => $transaction->created_at ? $transaction->created_at->format('Y-m-d H:i:s') : now()->format('Y-m-d H:i:s'),
                'nama_pembeli' => $transaction->customer_phone ?? 'Pelanggan Topup Wistek',
                'total' => (float) $transaction->price,
                'category_name' => $transaction->category_name,
                'product_name' => $transaction->product_name,
                'payment_method' => $transaction->payment_method,
            ]);

            if ($response->successful() && $response->json('success') === true) {
                $transaction->update([
                    'is_synced_to_simup' => true,
                    'synced_to_simup_at' => now(),
                ]);

                return true;
            }

            logger()->error('Failed to sync transaction to Simup: '.$response->body());

            return false;
        } catch (\Throwable $e) {
            logger()->error('Exception syncing transaction to Simup: '.$e->getMessage());

            return false;
        }
    }

    /**
     * Batch sync all unsynced paid transactions to simup_wistek server
     */
    public function syncPendingTransactions(): array
    {
        $pendingTransactions = Transaction::where('payment_status', 'paid')
            ->where(function ($query) {
                $query->where('is_synced_to_simup', false)
                    ->orWhereNull('is_synced_to_simup');
            })
            ->get();

        $successCount = 0;
        $failedCount = 0;

        foreach ($pendingTransactions as $transaction) {
            if ($this->syncTransaction($transaction)) {
                $successCount++;
            } else {
                $failedCount++;
            }
        }

        return [
            'total' => $pendingTransactions->count(),
            'success' => $successCount,
            'failed' => $failedCount,
        ];
    }

    /**
     * Live test connection to simup_wistek server
     */
    public function checkConnection(): array
    {
        $enabled = Setting::get('simup_enabled', '1') === '1';
        if (! $enabled) {
            return [
                'success' => false,
                'message' => 'Status Fitur: Nonaktif (Silakan aktifkan terlebih dahulu)',
            ];
        }

        $simupUrl = Setting::get('simup_webhook_url', config('services.simup.url'));
        $simupSecret = Setting::get('simup_webhook_secret', config('services.simup.secret'));

        if (empty($simupUrl) || empty($simupSecret)) {
            return [
                'success' => false,
                'message' => 'URL Server atau Secret Key belum diisi',
            ];
        }

        $endpoint = rtrim($simupUrl, '/').'/api/v1/webhook/topup-income';

        try {
            $response = Http::withHeaders([
                'X-Wistek-Secret' => $simupSecret,
                'Accept' => 'application/json',
            ])->timeout(5)->post($endpoint, [
                'kode_transaksi' => 'PING_CHECK',
                'total' => 0,
            ]);

            if ($response->status() === 401) {
                return [
                    'success' => false,
                    'message' => 'URL Server terhubung tetapi Secret Key Token tidak cocok (401 Unauthorized)',
                ];
            }

            if ($response->successful()) {
                return [
                    'success' => true,
                    'message' => 'Terhubung Ke Server SIMUP (OK)',
                ];
            }

            return [
                'success' => false,
                'message' => 'Server SIMUP merespon status HTTP: '.$response->status(),
            ];
        } catch (\Throwable $e) {
            return [
                'success' => false,
                'message' => 'Gagal terhubung ke server SIMUP: '.$e->getMessage(),
            ];
        }
    }
}
