<?php

namespace App\Filament\Resources\Transactions\Schemas;

use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Schema;

class TransactionInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextEntry::make('invoice'),
                TextEntry::make('reference')
                    ->placeholder('-'),
                TextEntry::make('category_name'),
                TextEntry::make('product_name'),
                TextEntry::make('sku')
                    ->label('SKU'),
                TextEntry::make('target_no'),
                TextEntry::make('price')
                    ->money(),
                TextEntry::make('payment_method'),
                TextEntry::make('payment_status'),
                TextEntry::make('topup_status'),
                TextEntry::make('note')
                    ->placeholder('-')
                    ->columnSpanFull(),
                TextEntry::make('created_at')
                    ->dateTime()
                    ->placeholder('-'),
                TextEntry::make('updated_at')
                    ->dateTime()
                    ->placeholder('-'),
            ]);
    }
}
