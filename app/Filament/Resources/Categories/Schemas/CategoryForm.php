<?php

namespace App\Filament\Resources\Categories\Schemas;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
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
                Toggle::make('status')
                    ->label('Aktif / Tampilkan di Web')
                    ->default(true)
                    ->required(),
            ]);
    }
}
