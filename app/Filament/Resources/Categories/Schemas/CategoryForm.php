<?php

namespace App\Filament\Resources\Categories\Schemas;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Schema;
use Illuminate\Support\Str;

class CategoryForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('name')
                    ->label('Nama Kategori / Jenis Topup')
                    ->required()
                    ->live(onBlur: true)
                    ->afterStateUpdated(fn (string $operation, $state, callable $set) => $operation === 'create' ? $set('slug', Str::slug($state)) : null
                    ),
                TextInput::make('slug')
                    ->label('Slug URL')
                    ->required()
                    ->unique(ignoreRecord: true),
                TextInput::make('thumbnail')
                    ->label('URL Gambar Thumbnail')
                    ->url()
                    ->placeholder('https://example.com/logo.png')
                    ->helperText('Masukkan URL gambar logo untuk kategori ini.'),
                Select::make('type')
                    ->label('Tipe Kategori')
                    ->required()
                    ->options([
                        'game' => 'Game',
                        'pulsa' => 'Pulsa & Data',
                        'emoney' => 'E-Money',
                        'streaming' => 'Streaming & Hiburan',
                        'pln' => 'PLN / Listrik',
                        'tagihan' => 'Tagihan & PPOB',
                        'voucher' => 'Voucher',
                    ]),
                TextInput::make('sort_order')
                    ->label('Urutan Tampilan')
                    ->numeric()
                    ->default(0)
                    ->helperText('Makin kecil angkanya (misal 1, 2, 3...), makin depan posisinya di halaman utama.'),
                Toggle::make('status')
                    ->label('Aktif / Tampilkan di Web')
                    ->default(true)
                    ->required(),
                Toggle::make('is_nickname_check_enabled')
                    ->label('Aktifkan Cek Username')
                    ->default(true)
                    ->live()
                    ->helperText('Aktifkan atau matikan pencarian nama/nickname pada form masukkan data akun.'),
                Select::make('nickname_check_provider')
                    ->label('Mode Provider Pengecekan')
                    ->default('public')
                    ->options([
                        'public' => 'API Publik Gratis (Rekomendasi Utama)',
                        'digiflazz' => 'Digiflazz Inquiry SKU (Resmi 99% Akurat)',
                        'disabled' => 'Nonaktifkan Pengecekan',
                    ])
                    ->live()
                    ->visible(fn (Get $get): bool => (bool) $get('is_nickname_check_enabled')),
                TextInput::make('digiflazz_inquiry_sku')
                    ->label('SKU Digiflazz Cek Username')
                    ->placeholder('Contoh: pre33614125')
                    ->helperText('Masukkan kode SKU Digiflazz Cek Username (seperti pre33614125).')
                    ->visible(fn (Get $get): bool => $get('is_nickname_check_enabled') && $get('nickname_check_provider') === 'digiflazz'),
            ]);
    }
}
