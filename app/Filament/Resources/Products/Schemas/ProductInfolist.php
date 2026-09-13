<?php

namespace App\Filament\Resources\Products\Schemas;

use App\Services\DigiflazzService;
use Filament\Infolists\Components\IconEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Schema;

class ProductInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextEntry::make('category.name')
                    ->label('Kategori'),
                TextEntry::make('name')
                    ->label('Nama Produk / Nominal'),
                TextEntry::make('sku')
                    ->label('SKU Digiflazz'),
                TextEntry::make('price_cost')
                    ->label('Harga Modal')
                    ->formatStateUsing(fn ($state) => 'Rp '.number_format((float) $state, 0, ',', '.')),
                TextEntry::make('price_sell')
                    ->label('Harga Regular (Publik)')
                    ->formatStateUsing(fn ($state) => 'Rp '.number_format((float) $state, 0, ',', '.')),
                TextEntry::make('price_gold')
                    ->label('Harga Gold (VIP)')
                    ->state(fn ($record) => 'Rp '.number_format($record->price_gold > 0 ? (float) $record->price_gold : DigiflazzService::calculateGoldPrice((float) $record->price_cost), 0, ',', '.')),
                TextEntry::make('price_platinum')
                    ->label('Harga Platinum (VVIP)')
                    ->state(fn ($record) => 'Rp '.number_format($record->price_platinum > 0 ? (float) $record->price_platinum : DigiflazzService::calculatePlatinumPrice((float) $record->price_cost), 0, ',', '.')),
                TextEntry::make('price_cash')
                    ->label('Harga Cash (Kasir)')
                    ->state(fn ($record) => 'Rp '.number_format($record->price_cash > 0 ? (float) $record->price_cash : DigiflazzService::calculateFinalPriceCash((float) $record->price_cost), 0, ',', '.')),
                IconEntry::make('status')
                    ->label('Status Website')
                    ->boolean(),
                IconEntry::make('digiflazz_status')
                    ->label('Status Provider (Digiflazz)')
                    ->boolean(),
                TextEntry::make('created_at')
                    ->label('Dibuat Pada')
                    ->dateTime()
                    ->placeholder('-'),
                TextEntry::make('updated_at')
                    ->label('Diperbarui Pada')
                    ->dateTime()
                    ->placeholder('-'),
            ]);
    }
}
