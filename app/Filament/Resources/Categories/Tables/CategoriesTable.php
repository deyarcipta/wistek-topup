<?php

namespace App\Filament\Resources\Categories\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ToggleColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class CategoriesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                ImageColumn::make('thumbnail')
                    ->label('Logo')
                    ->circular(),
                TextColumn::make('name')
                    ->label('Nama Kategori')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('slug')
                    ->label('Slug')
                    ->searchable(),
                TextColumn::make('type')
                    ->label('Tipe')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'game' => 'success',
                        'pulsa' => 'warning',
                        'emoney' => 'info',
                        'streaming' => 'danger',
                        'pln' => 'primary',
                        'tagihan' => 'gray',
                        'voucher' => 'danger',
                        default => 'gray',
                    })
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'game' => 'Game',
                        'pulsa' => 'Pulsa & Data',
                        'emoney' => 'E-Money',
                        'streaming' => 'Streaming & Hiburan',
                        'pln' => 'PLN / Listrik',
                        'tagihan' => 'Tagihan & PPOB',
                        'voucher' => 'Voucher',
                        default => ucfirst($state),
                    })
                    ->searchable()
                    ->sortable(),
                ToggleColumn::make('status')
                    ->label('Aktif / Tampilkan')
                    ->sortable(),
                TextColumn::make('created_at')
                    ->label('Dibuat Pada')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('updated_at')
                    ->label('Diperbarui Pada')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('type')
                    ->label('Tipe')
                    ->options([
                        'game' => 'Game',
                        'pulsa' => 'Pulsa & Data',
                        'emoney' => 'E-Money',
                        'streaming' => 'Streaming & Hiburan',
                        'pln' => 'PLN / Listrik',
                        'tagihan' => 'Tagihan & PPOB',
                        'voucher' => 'Voucher',
                    ]),
                SelectFilter::make('status')
                    ->label('Status Aktif')
                    ->options([
                        '1' => 'Aktif',
                        '0' => 'Nonaktif',
                    ]),
            ])
            ->recordActions([
                ViewAction::make(),
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
