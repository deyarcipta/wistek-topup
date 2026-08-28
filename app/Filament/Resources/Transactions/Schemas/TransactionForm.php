<?php

namespace App\Filament\Resources\Transactions\Schemas;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class TransactionForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('invoice')
                    ->label('Nomor Invoice')
                    ->disabled()
                    ->required(),
                TextInput::make('reference')
                    ->label('Referensi Duitku')
                    ->disabled(),
                TextInput::make('category_name')
                    ->label('Kategori')
                    ->disabled()
                    ->required(),
                TextInput::make('product_name')
                    ->label('Nama Produk')
                    ->disabled()
                    ->required(),
                TextInput::make('sku')
                    ->label('SKU Digiflazz')
                    ->disabled()
                    ->required(),
                TextInput::make('target_no')
                    ->label('Nomor Target / Tujuan')
                    ->disabled()
                    ->required(),
                TextInput::make('price')
                    ->label('Harga')
                    ->disabled()
                    ->required()
                    ->numeric()
                    ->prefix('Rp'),
                TextInput::make('payment_method')
                    ->label('Metode Pembayaran')
                    ->disabled()
                    ->required(),
                Select::make('payment_status')
                    ->label('Status Pembayaran')
                    ->required()
                    ->options([
                        'unpaid' => 'Belum Lunas (Unpaid)',
                        'paid' => 'Lunas (Paid)',
                        'expired' => 'Kedaluwarsa (Expired)',
                        'failed' => 'Gagal (Failed)',
                    ]),
                Select::make('topup_status')
                    ->label('Status Topup')
                    ->required()
                    ->options([
                        'pending' => 'Menunggu (Pending)',
                        'processing' => 'Diproses (Processing)',
                        'success' => 'Sukses (Success)',
                        'failed' => 'Gagal (Failed)',
                    ]),
                Textarea::make('note')
                    ->label('Catatan / Serial Number (SN)')
                    ->columnSpanFull(),
                Textarea::make('payment_details')
                    ->label('Detail Respon Gateway (JSON)')
                    ->disabled()
                    ->formatStateUsing(fn ($state) => is_array($state) ? json_encode($state, JSON_PRETTY_PRINT) : $state)
                    ->columnSpanFull(),
            ]);
    }
}
