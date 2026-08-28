<?php

namespace App\Filament\Resources\Vouchers\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class VouchersTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('code')
                    ->label('Kode Voucher')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('type')
                    ->label('Tipe Potongan')
                    ->formatStateUsing(fn ($state) => $state === 'percent' ? 'Persentase (%)' : 'Nominal Rupiah')
                    ->sortable(),
                TextColumn::make('value')
                    ->label('Nilai Potongan')
                    ->formatStateUsing(fn ($record) => $record->type === 'percent' ? $record->value.'%' : 'Rp '.number_format($record->value, 0, ',', '.'))
                    ->sortable(),
                TextColumn::make('max_uses')
                    ->label('Kuota Maksimal')
                    ->formatStateUsing(fn ($state) => $state == 0 ? '∞ (Tidak Terbatas)' : $state)
                    ->sortable(),
                TextColumn::make('used_count')
                    ->label('Telah Digunakan')
                    ->sortable(),
                TextColumn::make('valid_until')
                    ->label('Berlaku Hingga')
                    ->dateTime()
                    ->sortable(),
                IconColumn::make('is_active')
                    ->label('Aktif')
                    ->boolean()
                    ->sortable(),
            ])
            ->filters([
                //
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
