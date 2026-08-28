<?php

namespace App\Filament\Widgets;

use App\Models\Transaction;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class StatsOverview extends StatsOverviewWidget
{
    protected function getStats(): array
    {
        // 1. Total Revenue (Paid)
        $revenue = Transaction::where('payment_status', 'paid')->sum('price');

        // 2. Total Successful Topups
        $successCount = Transaction::where('topup_status', 'success')->count();

        // 3. Total Pending Transactions
        $pendingCount = Transaction::where('payment_status', 'unpaid')->count();

        // 4. Total Failed Transactions (either payment failed or topup failed)
        $failedCount = Transaction::where('payment_status', 'failed')
            ->orWhere('topup_status', 'failed')
            ->count();

        return [
            Stat::make('Total Pendapatan', 'Rp '.number_format($revenue, 0, ',', '.'))
                ->description('Total uang masuk dari transaksi lunas')
                ->descriptionIcon('heroicon-m-banknotes')
                ->color('success'),
            Stat::make('Topup Sukses', $successCount)
                ->description('Jumlah topup yang berhasil dikirim')
                ->descriptionIcon('heroicon-m-check-circle')
                ->color('success'),
            Stat::make('Transaksi Pending', $pendingCount)
                ->description('Menunggu pembayaran pelanggan')
                ->descriptionIcon('heroicon-m-clock')
                ->color('warning'),
            Stat::make('Transaksi Gagal', $failedCount)
                ->description('Pembayaran gagal atau topup gagal')
                ->descriptionIcon('heroicon-m-x-circle')
                ->color('danger'),
        ];
    }
}
