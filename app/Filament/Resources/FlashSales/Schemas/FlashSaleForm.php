<?php

namespace App\Filament\Resources\FlashSales\Schemas;

use App\Models\Product;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Schema;

class FlashSaleForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('title')
                    ->label('Judul Promo Flash Sale')
                    ->default('Flash Sale Special ⚡')
                    ->required(),
                Select::make('product_id')
                    ->label('Pilih Produk Game')
                    ->required()
                    ->searchable()
                    ->options(function () {
                        return Product::with('category')
                            ->where('status', true)
                            ->get()
                            ->mapWithKeys(function ($p) {
                                $cat = $p->category ? $p->category->name : 'General';

                                return [$p->id => "{$cat} - {$p->name} (Harga Normal: Rp ".number_format($p->price_sell, 0, ',', '.').')'];
                            });
                    })
                    ->live()
                    ->afterStateUpdated(function (Set $set, $state) {
                        if ($state) {
                            $product = Product::find($state);
                            if ($product) {
                                // Default discount 15%
                                $set('discount_price', round($product->price_sell * 0.85));
                            }
                        }
                    }),
                TextInput::make('discount_price')
                    ->label('Harga Diskon Flash Sale (Rp)')
                    ->numeric()
                    ->prefix('Rp')
                    ->required()
                    ->helperText('Masukkan harga promo khusus Flash Sale. Harus lebih murah dari harga normal.'),
                TextInput::make('stock_total')
                    ->label('Total Kuota Promo')
                    ->numeric()
                    ->default(20)
                    ->required()
                    ->helperText('Jumlah kuota ketersediaan item untuk promo Flash Sale ini.'),
                TextInput::make('stock_sold')
                    ->label('Jumlah Terjual (Progress Bar)')
                    ->numeric()
                    ->default(0)
                    ->required()
                    ->helperText('Angka ini menentukan persentase progress bar kuota tersisa di halaman web.'),
                DateTimePicker::make('start_at')
                    ->label('Waktu Mulai Promo')
                    ->default(now())
                    ->required(),
                DateTimePicker::make('end_at')
                    ->label('Waktu Berakhir (Countdown Timer)')
                    ->default(now()->addHours(24))
                    ->required()
                    ->helperText('Timer hitung mundur digital di halaman utama akan berjalan hingga waktu ini.'),
                Toggle::make('is_active')
                    ->label('Aktifkan Promo Flash Sale')
                    ->default(true)
                    ->required(),
            ]);
    }
}
