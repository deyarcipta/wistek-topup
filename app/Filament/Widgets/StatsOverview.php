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

        // 2. Payment Gateway Status & Revenue collected via Online Payment Gateway
        $paymentManager = new PaymentGatewayManager;
        $gatewayName = $paymentManager->getActiveGatewayName();
        $activeGatewayKey = strtoupper($paymentManager->getActiveGateway());

        $gatewayRevenue = Transaction::where('payment_status', 'paid')
            ->where('payment_method', '!=', 'CASH')
            ->sum('price');
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
                ->descriptionIcon('heroicon-m-wallet')
                ->color($dfColor),

            Stat::make('Payment Gateway ('.$activeGatewayKey.')', $formattedGatewayRevenue)
                ->description($gatewayName.' - Omset Online')
                ->descriptionIcon('heroicon-m-credit-card')
                ->color('info'),

            Stat::make('Total Omset Lunas', 'Rp '.number_format($revenue, 0, ',', '.'))
                ->description('Total pendapatan (Online + Cash)')
                ->descriptionIcon('heroicon-m-banknotes')
                ->color('success'),

            Stat::make('Topup Sukses', number_format($successCount, 0, ',', '.'))
                ->description('Jumlah produk berhasil terkirim')
                ->descriptionIcon('heroicon-m-check-circle')
                ->color('success'),

            Stat::make('Transaksi Pending', number_format($pendingCount, 0, ',', '.'))
                ->description('Menunggu pembayaran pelanggan')
                ->descriptionIcon('heroicon-m-clock')
                ->color('warning'),

            Stat::make('Transaksi Gagal', number_format($failedCount, 0, ',', '.'))
                ->description('Pembayaran/topup gagal')
                ->descriptionIcon('heroicon-m-x-circle')
                ->color('danger'),
        ];
    }
}
