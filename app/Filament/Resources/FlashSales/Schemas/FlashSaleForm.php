<?php

namespace App\Filament\Resources\FlashSales\Schemas;

use App\Models\Product;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Utilities\Get;
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
                    ->afterStateUpdated(function (Set $set, Get $get, $state) {
                        if ($state) {
                            $product = Product::find($state);
                            if ($product && (float) $product->price_sell > 0) {
                                $percent = $get('discount_percent');
                                if ($percent !== null && $percent !== '') {
                                    $discountPrice = round((float) $product->price_sell * (1 - ((float) $percent / 100)));
                                    $set('discount_price', max(0, $discountPrice));
                                } else {
                                    // Default discount 15%
                                    $set('discount_percent', 15);
                                    $set('discount_price', round((float) $product->price_sell * 0.85));
                                }
                            }
                        }
                    }),
                TextInput::make('discount_percent')
                    ->label('Persentase Diskon (%)')
                    ->numeric()
                    ->suffix('%')
                    ->minValue(0)
                    ->maxValue(99)
                    ->live(onBlur: true)
                    ->dehydrated(false)
                    ->afterStateHydrated(function (Set $set, Get $get, $record) {
                        if ($record && $record->product && (float) $record->product->price_sell > 0 && $record->discount_price) {
                            $orig = (float) $record->product->price_sell;
                            $disc = (float) $record->discount_price;
                            if ($disc < $orig) {
                                $amount = $orig - $disc;
                                $percent = (int) round(($amount / $orig) * 100);
                                $set('discount_percent', max(0, min(99, $percent)));
                                $set('discount_amount', (int) $amount);
                            }
                        }
                    })
                    ->afterStateUpdated(function (Set $set, Get $get, $state) {
                        $productId = $get('product_id');
                        if ($productId && $state !== null && $state !== '') {
                            $product = Product::find($productId);
                            if ($product && (float) $product->price_sell > 0) {
                                $orig = (float) $product->price_sell;
                                $percent = (float) $state;
                                $amount = round($orig * ($percent / 100));
                                $set('discount_amount', $amount);
                                $set('discount_price', max(0, $orig - $amount));
                            }
                        }
                    })
                    ->helperText('Otomatis menghitung Nominal Potongan Diskon (Rp) & Harga Akhir Promo.'),
                TextInput::make('discount_amount')
                    ->label('Nominal Potongan Diskon (Rp)')
                    ->numeric()
                    ->prefix('Rp')
                    ->live(onBlur: true)
                    ->dehydrated(false)
                    ->afterStateHydrated(function (Set $set, Get $get, $record) {
                        if ($record && $record->product && (float) $record->product->price_sell > 0 && $record->discount_price) {
                            $orig = (float) $record->product->price_sell;
                            $disc = (float) $record->discount_price;
                            if ($disc < $orig) {
                                $amount = $orig - $disc;
                                $set('discount_amount', (int) $amount);
                            }
                        }
                    })
                    ->afterStateUpdated(function (Set $set, Get $get, $state) {
                        $productId = $get('product_id');
                        if ($productId && $state !== null && $state !== '') {
                            $product = Product::find($productId);
                            if ($product && (float) $product->price_sell > 0) {
                                $orig = (float) $product->price_sell;
                                $amount = (float) $state;
                                $percent = (int) round(($amount / $orig) * 100);
                                $set('discount_percent', max(0, min(99, $percent)));
                                $set('discount_price', max(0, $orig - $amount));
                            }
                        }
                    })
                    ->helperText('Ketik nominal misal 2000, maka Persentase (10%) & Harga Akhir Promo otomatis terisi.'),
                TextInput::make('discount_price')
                    ->label('Harga Promo Akhir (Rp)')
                    ->numeric()
                    ->prefix('Rp')
                    ->required()
                    ->live(onBlur: true)
                    ->afterStateUpdated(function (Set $set, Get $get, $state) {
                        $productId = $get('product_id');
                        if ($productId && $state !== null && $state !== '') {
                            $product = Product::find($productId);
                            if ($product && (float) $product->price_sell > 0) {
                                $orig = (float) $product->price_sell;
                                $discPrice = (float) $state;
                                $amount = max(0, $orig - $discPrice);
                                $percent = (int) round(($amount / $orig) * 100);
                                $set('discount_amount', (int) $amount);
                                $set('discount_percent', max(0, min(99, $percent)));
                            }
                        }
                    })
                    ->helperText('Otomatis menghitung Persentase (%) & Nominal Potongan (Rp) saat harga ini diubah.'),
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
