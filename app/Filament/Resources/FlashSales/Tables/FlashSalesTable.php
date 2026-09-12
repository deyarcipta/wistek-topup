<?php

namespace App\Filament\Resources\FlashSales\Tables;

use App\Models\FlashSale;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ToggleColumn;
use Filament\Tables\Table;

class FlashSalesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('title')
                    ->label('Judul Event')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('product.name')
                    ->label('Produk')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('product.price_sell')
                    ->label('Harga Normal')
                    ->formatStateUsing(fn ($state) => 'Rp '.number_format($state, 0, ',', '.'))
                    ->sortable(),
                TextColumn::make('discount_price')
                    ->label('Harga Flash Sale')
                    ->formatStateUsing(fn ($state) => 'Rp '.number_format($state, 0, ',', '.'))
                    ->color('success')
                    ->weight('bold')
                    ->sortable(),
                TextColumn::make('discount_percentage')
                    ->label('Diskon')
                    ->badge()
                    ->color('danger')
                    ->formatStateUsing(fn ($state) => "{$state}% OFF"),
                TextColumn::make('stock_progress')
                    ->label('Kuota Terjual')
                    ->state(fn (FlashSale $record): string => "{$record->stock_sold} / {$record->stock_total}"),
                TextColumn::make('end_at')
                    ->label('Selesai Pada')
                    ->dateTime('d M Y, H:i')
                    ->sortable(),
                ToggleColumn::make('is_active')
                    ->label('Aktif')
                    ->sortable(),
            ])
            ->recordActions([
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('created_at', 'desc');
    }
}
