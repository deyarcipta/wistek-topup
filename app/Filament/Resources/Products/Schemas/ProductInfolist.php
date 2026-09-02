<?php

namespace App\Filament\Resources\Products\Schemas;

use Filament\Infolists\Components\IconEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Schema;

class ProductInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextEntry::make('category_id')
                    ->numeric(),
                TextEntry::make('name'),
                TextEntry::make('sku')
                    ->label('SKU'),
                TextEntry::make('price_cost')
                    ->label('Harga Modal')
                    ->formatStateUsing(fn ($state) => 'Rp '.number_format((float) $state, 0, ',', '.')),
                TextEntry::make('price_sell')
                    ->label('Harga Jual')
                    ->formatStateUsing(fn ($state) => 'Rp '.number_format((float) $state, 0, ',', '.')),
                IconEntry::make('status')
                    ->boolean(),
                TextEntry::make('created_at')
                    ->dateTime()
                    ->placeholder('-'),
                TextEntry::make('updated_at')
                    ->dateTime()
                    ->placeholder('-'),
            ]);
    }
}
