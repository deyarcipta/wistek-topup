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
