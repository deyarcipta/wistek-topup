<?php

namespace App\Filament\Resources\SubCategories\Schemas;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class SubCategoryForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('category_id')
                    ->relationship('category', 'name')
                    ->label('Kategori Utama (Game)')
                    ->searchable()
                    ->preload()
                    ->required(),
                TextInput::make('name')
                    ->label('Nama Sub-kategori')
                    ->required()
                    ->placeholder('Misal: Weekly Diamond Pass atau Special Items'),
                TextInput::make('sort_order')
                    ->label('Urutan Tampilan')
                    ->numeric()
                    ->default(0)
                    ->placeholder('Misal: 1, 2, 3')
                    ->helperText('Grup dengan angka lebih kecil akan diletakkan paling atas.'),
            ]);
    }
}
