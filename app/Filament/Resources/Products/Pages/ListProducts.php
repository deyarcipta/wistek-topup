<?php

namespace App\Filament\Resources\Products\Pages;

use App\Filament\Resources\Products\ProductResource;
use App\Services\DigiflazzService;
use Filament\Actions\Action;
use Filament\Actions\CreateAction;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ListRecords;

class ListProducts extends ListRecords
{
    protected static string $resource = ProductResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Action::make('autoSwitchSeller')
                ->label('⚡ Switch Seller Termurah')
                ->icon('heroicon-o-bolt')
                ->color('warning')
                ->requiresConfirmation()
                ->modalHeading('Otomatiskan & Switch Seller Termurah')
                ->modalDescription('Sistem akan memindai seluruh seller di Digiflazz, memilih seller teraktif dengan harga modal terendah, dan otomatis mengalihkan SKU produk ke seller termurah saat ini.')
                ->modalSubmitActionLabel('Jalankan Switch Seller Termurah')
                ->action(function () {
                    try {
                        $digiflazz = new DigiflazzService;
                        $result = $digiflazz->syncProducts(true);

                        Notification::make()
                            ->title('Seller Termurah Berhasil Dialihkan!')
                            ->body("Sistem telah memindai {$result['total']} produk. {$result['updated']} produk berhasil diperbarui/dialihkan ke seller teraktif dengan harga termurah.")
                            ->success()
                            ->send();
                    } catch (\Exception $e) {
                        Notification::make()
                            ->title('Gagal Melakukan Switch Seller')
                            ->body($e->getMessage())
                            ->danger()
                            ->send();
                    }
                }),
            Action::make('syncDigiflazz')
                ->label('Sinkronkan Digiflazz')
                ->icon('heroicon-o-arrow-path')
                ->color('success')
                ->requiresConfirmation()
                ->modalHeading('Sinkronisasi Produk Digiflazz')
                ->modalDescription('Sistem akan mengambil semua produk aktif dari akun Digiflazz Anda, mengimpor produk baru secara otomatis, dan memperbarui harga modal.')
                ->modalSubmitActionLabel('Mulai Sinkronisasi')
                ->action(function () {
                    try {
                        $digiflazz = new DigiflazzService;
                        $result = $digiflazz->syncProducts(true);

                        Notification::make()
                            ->title('Sinkronisasi Berhasil!')
                            ->body("Total {$result['total']} produk diproses. {$result['created']} produk baru ditambahkan, {$result['updated']} diperbarui, {$result['deactivated']} dinonaktifkan/gangguan.")
                            ->success()
                            ->send();
                    } catch (\Exception $e) {
                        Notification::make()
                            ->title('Sinkronisasi Gagal')
                            ->body($e->getMessage())
                            ->danger()
                            ->send();
                    }
                }),
            CreateAction::make(),
        ];
    }
}
