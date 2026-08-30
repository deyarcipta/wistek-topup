<?php

namespace App\Filament\Resources\Vouchers\Schemas;

use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Schema;

class VoucherForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('code')
                    ->label('Kode Voucher')
                    ->required()
                    ->unique(ignoreRecord: true)
                    ->placeholder('Misal: DISKON5K, PROMO10'),
                Select::make('type')
                    ->label('Tipe Potongan')
                    ->options([
                        'fixed' => 'Potongan Rupiah (Fixed)',
                        'percent' => 'Potongan Persentase (%)',
                    ])
                    ->required()
                    ->default('fixed')
                    ->live(),
                TextInput::make('value')
                    ->label('Nilai Potongan')
                    ->numeric()
                    ->required()
                    ->placeholder('Misal: 5000 (untuk Rupiah) atau 10 (untuk 10%)'),
                TextInput::make('min_purchase')
                    ->label('Minimal Pembelian (Rp)')
                    ->numeric()
                    ->default(0)
                    ->placeholder('Contoh: 10000, 20000, 50000')
                    ->helperText('Minimal harga produk agar voucher ini dapat digunakan. Isi 0 jika tanpa syarat minimal.'),
                TextInput::make('max_discount')
                    ->label('Maksimal Potongan Diskon (Rp)')
                    ->numeric()
                    ->nullable()
                    ->placeholder('Contoh: 5000 (Kosongkan jika tanpa batas)')
                    ->helperText('Batas maksimal diskon khusus tipe persentase (%) agar tidak melampaui margin keuntungan.')
                    ->visible(fn (Get $get): bool => $get('type') === 'percent'),
                TextInput::make('max_uses')
                    ->label('Maksimal Kuota Penggunaan')
                    ->numeric()
                    ->default(0)
                    ->helperText('Isi 0 untuk kuota tidak terbatas'),
                TextInput::make('used_count')
                    ->label('Jumlah Pemakaian')
                    ->numeric()
                    ->default(0)
                    ->disabled()
                    ->dehydrated(false),
                DateTimePicker::make('valid_until')
                    ->label('Berlaku Hingga')
                    ->nullable(),
                Toggle::make('is_active')
                    ->label('Status Aktif')
                    ->default(true)
                    ->required(),
            ]);
    }
}
