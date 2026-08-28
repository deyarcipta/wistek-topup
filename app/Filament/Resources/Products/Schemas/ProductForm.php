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
                    ->label('Harga Jual')
                    ->numeric()
                    ->prefix('Rp')
                    ->required(),
                Toggle::make('status')
                    ->label('Aktif / Tampilkan')
                    ->default(true)
                    ->required(),
            ]);
    }
}
