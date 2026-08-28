<?php

namespace App\Filament\Resources\Banners\Schemas;

use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;

class BannerForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('title')
                    ->label('Judul / Keterangan Slide (Opsional)')
                    ->placeholder('Misal: Event Promo Diamond Mobile Legends')
                    ->nullable(),
                FileUpload::make('image_path')
                    ->label('Gambar Banner')
                    ->image()
                    ->disk('public')
                    ->directory('banners')
                    ->visibility('public')
                    ->required()
                    ->helperText('Gunakan gambar dengan rasio lebar (rekomendasi: 1200x500 atau 16:9)'),
                TextInput::make('link_url')
                    ->label('Link URL Tujuan (Opsional)')
                    ->placeholder('Misal: /category/mobile-legends')
                    ->nullable(),
                TextInput::make('sort_order')
                    ->label('Urutan Tampilan')
                    ->numeric()
                    ->default(0)
                    ->required(),
                Toggle::make('is_active')
                    ->label('Aktif')
                    ->default(true)
                    ->required(),
            ]);
    }
}
