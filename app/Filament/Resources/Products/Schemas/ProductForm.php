<?php

namespace App\Filament\Resources\Products\Schemas;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;

class ProductForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('category_id')
                    ->relationship('category', 'name')
                    ->label('Kategori')
                    ->searchable()
                    ->preload()
                    ->live()
                    ->required(),
                TextInput::make('name')
                    ->label('Nama Produk / Nominal')
                    ->required()
                    ->placeholder('Misal: 86 Diamonds atau Pulsa 5.000'),
                Select::make('sub_category_id')
                    ->label('Sub-kategori / Grup')
                    ->relationship(
                        name: 'subCategory',
                        titleAttribute: 'name',
                        modifyQueryUsing: fn ($query, $get) => $query->where('category_id', $get('category_id')),
                    )
                    ->searchable()
                    ->preload()
                    ->placeholder('Pilih sub-kategori')
                    ->helperText('Kosongkan jika ingin masuk ke grup default. Kelola sub-kategori di menu "Sub Kategori".'),
                TextInput::make('sku')
                    ->label('SKU Digiflazz')
                    ->required()
                    ->placeholder('Misal: MLBB86 atau H5'),
                TextInput::make('price_cost')
                    ->label('Harga Beli (Modal)')
                    ->numeric()
                    ->prefix('Rp')
                    ->required(),
                TextInput::make('price_sell')
                    ->label('Harga Jual (Publik / Regular)')
                    ->numeric()
                    ->prefix('Rp')
                    ->required(),
                TextInput::make('price_gold')
                    ->label('Harga Khusus Gold (VIP)')
                    ->numeric()
                    ->prefix('Rp')
                    ->placeholder('Opsional (opsional jika sama dengan harga publik)')
                    ->helperText('Harga khusus member level Gold / VIP. Jika dikosongkan, sistem memakai Harga Publik.'),
                TextInput::make('price_platinum')
                    ->label('Harga Khusus Platinum (VVIP / Reseller)')
                    ->numeric()
                    ->prefix('Rp')
                    ->placeholder('Opsional (opsional jika sama dengan harga VIP)')
                    ->helperText('Harga grosir khusus member level Platinum / Reseller. Jika dikosongkan, sistem memakai Harga VIP / Publik.'),
                TextInput::make('price_cash')
                    ->label('Harga Cash (Petugas / Kasir)')
                    ->numeric()
                    ->prefix('Rp')
                    ->placeholder('Opsional (opsional jika sama dengan harga online)')
                    ->helperText('Harga khusus transaksi tunai oleh Petugas. Jika dikosongkan, sistem otomatis memakai Harga Jual Online.'),
                Toggle::make('status')
                    ->label('Tampilkan di Website')
                    ->helperText('Nyalakan untuk menampilkan dan menjual produk ini kepada pembeli di website.')
                    ->default(true)
                    ->required(),
                Toggle::make('digiflazz_status')
                    ->label('Status Provider (Digiflazz)')
                    ->helperText('Status otomatis dari Digiflazz (Aktif / Gangguan).')
                    ->default(true)
                    ->required(),
            ]);
    }
}
