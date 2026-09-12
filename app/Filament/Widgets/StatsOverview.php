<?php

namespace App\Filament\Widgets;

use App\Models\Transaction;
use App\Services\DigiflazzService;
use App\Services\PaymentGatewayManager;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class StatsOverview extends StatsOverviewWidget
{
    protected function getStats(): array
    {
        // 1. Real-time Digiflazz Deposit Balance
        $digiflazz = new DigiflazzService;
        $dfStatus = $digiflazz->getStatusDetails();

        if ($dfStatus['success']) {
            $dfBalance = (float) $dfStatus['balance'];
            $formattedDfBalance = 'Rp '.number_format($dfBalance, 0, ',', '.');
            $dfColor = match (true) {
                $dfBalance > 100000 => 'success',
                $dfBalance >= 20000 => 'warning',
                default => 'danger',
            };
            $dfDesc = $dfBalance < 50000 ? 'Saldo tipis! Segera isi ulang deposit' : 'Status Provider: Terhubung & Aktif';
        } else {
            $formattedDfBalance = 'Rp 0';
            $dfColor = 'danger';
            $dfDesc = 'Koneksi Provider: '.($dfStatus['message'] ?? 'Belum Konfigurasi');
        }

        // 2. Payment Gateway Status & Revenue collected via Online Payment Gateway (Exact Net Kliring Settlement after TriPay MDR)
        $paymentManager = new PaymentGatewayManager;
        $gatewayName = $paymentManager->getActiveGatewayName();
        $activeGatewayKey = strtoupper($paymentManager->getActiveGateway());

        $gatewayRevenue = Transaction::where('payment_status', 'paid')
            ->where('payment_method', '!=', 'CASH')
            ->get()
            ->sum(function ($tx) {
                $details = is_array($tx->payment_details) ? $tx->payment_details : [];

                // 1. If TriPay webhook provided exact amount_received, use it directly
                if (isset($details['amount_received']) && (float) $details['amount_received'] > 0) {
                    return (float) $details['amount_received'];
                }

                // 2. Otherwise calculate exact TriPay MDR Net Settlement per payment channel
                $method = strtoupper((string) $tx->payment_method);
                $price = (float) $tx->price;
                $adminFee = (float) ($details['admin_fee'] ?? 0);

                if (str_contains($method, 'QRIS')) {
                    // Standard TriPay QRIS MDR is 0.7%
                    $tripayMdrFee = round(($price * 0.7) / 100);

                    // Return net kliring amount received by merchant
                    return max(0, $price - $tripayMdrFee - $adminFee);
                } elseif (str_contains($method, 'BCA')) {
                    return max(0, $price - 5500 - $adminFee);
                } elseif (str_contains($method, 'VA') || str_contains($method, 'MYBVA')) {
                    return max(0, $price - 4250 - $adminFee);
                } elseif (in_array($method, ['ALFAMART', 'INDOMARET'])) {
                    return max(0, $price - 3500 - $adminFee);
                } elseif (in_array($method, ['OVO', 'DANA', 'SHOPEEPAY'])) {
                    $fee = max(1000, round(($price * 3.0) / 100));

                    return max(0, $price - $fee - $adminFee);
                }

                return max(0, $price - $adminFee);
            });
        $formattedGatewayRevenue = 'Rp '.number_format($gatewayRevenue, 0, ',', '.');

        // 3. General Transaction Metrics
        $revenue = Transaction::where('payment_status', 'paid')->sum('price');
        $successCount = Transaction::where('topup_status', 'success')->count();
        $pendingCount = Transaction::where('payment_status', 'unpaid')->count();
        $failedCount = Transaction::where('payment_status', 'failed')
            ->orWhere('topup_status', 'failed')
            ->count();

        return [
            Stat::make('Saldo Deposit Digiflazz', $formattedDfBalance)
                ->description($dfDesc)
                ->descriptionIcon('heroicon-o-wallet')
                ->color($dfColor),

            Stat::make('Payment Gateway ('.$activeGatewayKey.')', $formattedGatewayRevenue)
                ->description($gatewayName.' - Saldo Net Kliring')
                ->descriptionIcon('heroicon-o-credit-card')
                ->color('info'),

            Stat::make('Total Omset Lunas', 'Rp '.number_format($revenue, 0, ',', '.'))
                ->description('Total pendapatan (Online + Cash)')
                ->descriptionIcon('heroicon-o-banknotes')
                ->color('success'),

            Stat::make('Topup Sukses', number_format($successCount, 0, ',', '.'))
                ->description('Jumlah produk berhasil terkirim')
                ->descriptionIcon('heroicon-o-check-circle')
                ->color('success'),

            Stat::make('Transaksi Pending', number_format($pendingCount, 0, ',', '.'))
                ->description('Menunggu pembayaran pelanggan')
                ->descriptionIcon('heroicon-o-clock')
                ->color('warning'),

            Stat::make('Transaksi Gagal', number_format($failedCount, 0, ',', '.'))
                ->description('Pembayaran/topup gagal')
                ->descriptionIcon('heroicon-o-x-circle')
                ->color('danger'),
        ];

    }
}
