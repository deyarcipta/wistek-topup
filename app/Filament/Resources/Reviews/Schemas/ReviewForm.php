<?php

namespace App\Filament\Resources\Reviews\Schemas;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;

class ReviewForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('name')
                    ->label('Nama Pelanggan / Reviewer')
                    ->required()
                    ->placeholder('Misal: Rian Gamers, Amelia'),
                TextInput::make('role_or_title')
                    ->label('Label / Game / Keterangan')
                    ->placeholder('Misal: Mobile Legends Player, Survivor, Gamer')
                    ->default('Pelanggan'),
                Select::make('rating')
                    ->label('Rating Bintang')
                    ->options([
                        5 => '⭐⭐⭐⭐⭐ (5 Bintang - Sangat Puas)',
                        4 => '⭐⭐⭐⭐ (4 Bintang - Bagus)',
                        3 => '⭐⭐⭐ (3 Bintang - Cukup)',
                        2 => '⭐⭐ (2 Bintang - Kurang)',
                        1 => '⭐ (1 Bintang - Buruk)',
                    ])
                    ->required()
                    ->default(5),
                TextInput::make('sort_order')
                    ->label('Urutan Tampil')
                    ->numeric()
                    ->default(0)
                    ->helperText('Angka lebih kecil tampil lebih dulu di depan.'),
                Textarea::make('comment')
                    ->label('Isi Ulasan / Testimoni')
                    ->required()
                    ->rows(3)
                    ->columnSpanFull(),
                Toggle::make('is_visible')
                    ->label('Tampilkan ke Publik di Homepage')
                    ->default(true)
                    ->required(),
            ]);
    }
}
