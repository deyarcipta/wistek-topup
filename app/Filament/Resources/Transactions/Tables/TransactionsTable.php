<?php

namespace App\Filament\Resources\Transactions\Tables;

use App\Services\DigiflazzService;
use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Notifications\Notification;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class TransactionsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('invoice')
                    ->label('Invoice')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('reference')
                    ->label('Referensi Gateway')
                    ->searchable(),
                TextColumn::make('category_name')
                    ->label('Kategori')
                    ->searchable(),
                TextColumn::make('product_name')
                    ->label('Nama Produk')
                    ->searchable(),
                TextColumn::make('sku')
                    ->label('SKU')
                    ->searchable(),
                TextColumn::make('target_no')
                    ->label('No. Target')
                    ->searchable(),
                TextColumn::make('price')
                    ->label('Harga Jual')
                    ->formatStateUsing(fn ($state) => 'Rp '.number_format($state, 0, ',', '.'))
                    ->sortable(),
                TextColumn::make('profit')
                    ->label('Untung (Net)')
                    ->formatStateUsing(fn ($state) => 'Rp '.number_format((float) $state, 0, ',', '.'))
                    ->color('success')
                    ->sortable(),
                TextColumn::make('payment_method')
                    ->label('Pembayaran')
                    ->searchable(),
                TextColumn::make('payment_status')
                    ->label('Status Pembayaran')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'paid' => 'success',
                        'unpaid' => 'warning',
                        'expired' => 'gray',
                        'failed' => 'danger',
                        default => 'gray',
                    })
                    ->searchable()
                    ->sortable(),
                TextColumn::make('topup_status')
                    ->label('Status Topup')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'success' => 'success',
                        'pending' => 'warning',
                        'processing' => 'info',
                        'failed' => 'danger',
                        default => 'gray',
                    })
                    ->searchable()
                    ->sortable(),
                TextColumn::make('created_at')
                    ->label('Tanggal Transaksi')
                    ->dateTime()
                    ->sortable(),
                TextColumn::make('updated_at')
                    ->label('Terakhir Diupdate')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('payment_status')
                    ->label('Status Pembayaran')
                    ->options([
                        'unpaid' => 'Unpaid',
                        'paid' => 'Paid',
                        'expired' => 'Expired',
                        'failed' => 'Failed',
                    ]),
                SelectFilter::make('topup_status')
                    ->label('Status Topup')
                    ->options([
                        'pending' => 'Pending',
                        'processing' => 'Processing',
                        'success' => 'Success',
                        'failed' => 'Failed',
                    ]),
            ])
            ->recordActions([
                ViewAction::make(),
                EditAction::make(),
                Action::make('syncDigiflazzStatus')
                    ->label('Cek Status Provider')
                    ->icon('heroicon-m-arrow-path')
                    ->color('info')
                    ->visible(fn ($record) => $record->payment_status === 'paid' && in_array($record->topup_status, ['processing', 'pending']))
                    ->action(function ($record) {
                        try {
                            $digiflazz = new DigiflazzService;
                            $res = $digiflazz->checkTopupStatus($record);
                            if ($res['success']) {
                                Notification::make()
                                    ->title('Status Digiflazz Disinkronkan!')
                                    ->body('Status: '.strtoupper($res['status']).' | Catatan: '.($res['note'] ?? '-'))
                                    ->success()
                                    ->send();
                            } else {
                                Notification::make()
                                    ->title('Gagal Sinkronisasi Status')
                                    ->body($res['message'] ?? 'Eror koneksi')
                                    ->warning()
                                    ->send();
                            }
                        } catch (\Exception $e) {
                            Notification::make()
                                ->title('Terjadi Kesalahan')
                                ->body($e->getMessage())
                                ->danger()
                                ->send();
                        }
                    }),
                Action::make('retryDigiflazz')
                    ->label('Retry Digiflazz')
                    ->icon('heroicon-m-arrow-path')
                    ->color('warning')
                    ->visible(fn ($record) => $record->payment_status === 'paid' && in_array($record->topup_status, ['failed', 'processing', 'pending']))
                    ->requiresConfirmation()
                    ->modalHeading('Proses Ulang Pesanan ke Digiflazz')
                    ->modalDescription('Apakah Anda yakin ingin mengulang pengiriman pesanan ini ke provider Digiflazz?')
                    ->action(function ($record) {
                        try {
                            $digiflazz = new DigiflazzService;
                            $res = $digiflazz->orderTopupWithFailover($record);

                            if ($res['success']) {
                                $status = strtolower($res['data']['status'] ?? 'pending');
                                if ($status === 'sukses') {
                                    $record->update([
                                        'topup_status' => 'success',
                                        'note' => $res['data']['sn'] ?? 'Top-up Berhasil',
                                    ]);
                                    $record->creditPointsIfEligible();
                                    Notification::make()
                                        ->title('Topup Berhasil Diproses!')
                                        ->body('SN: '.($res['data']['sn'] ?? ''))
                                        ->success()
                                        ->send();
                                } elseif ($status === 'gagal') {
                                    $record->update([
                                        'topup_status' => 'failed',
                                        'note' => $res['data']['message'] ?? 'Gagal dari provider',
                                    ]);
                                    Notification::make()
                                        ->title('Proses Ulang Gagal dari Provider')
                                        ->body($res['data']['message'] ?? 'Gagal')
                                        ->danger()
                                        ->send();
                                } else {
                                    $record->update(['topup_status' => 'processing']);
                                    Notification::make()
                                        ->title('Pesanan Sedang Diproses Provider')
                                        ->info()
                                        ->send();
                                }
                            } else {
                                Notification::make()
                                    ->title('Gagal Mengirim ke Digiflazz')
                                    ->body($res['message'] ?? 'Eror koneksi')
                                    ->danger()
                                    ->send();
                            }
                        } catch (\Exception $e) {
                            Notification::make()
                                ->title('Terjadi Kesalahan')
                                ->body($e->getMessage())
                                ->danger()
                                ->send();
                        }
                    }),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('created_at', 'desc');
    }
}
