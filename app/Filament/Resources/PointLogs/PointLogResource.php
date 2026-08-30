<?php

namespace App\Filament\Resources\PointLogs;

use App\Filament\Resources\PointLogs\Pages\CreatePointLog;
use App\Filament\Resources\PointLogs\Pages\EditPointLog;
use App\Filament\Resources\PointLogs\Pages\ListPointLogs;
use App\Filament\Resources\PointLogs\Schemas\PointLogForm;
use App\Filament\Resources\PointLogs\Tables\PointLogsTable;
use App\Models\PointLog;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use UnitEnum;

class PointLogResource extends Resource
{
    protected static ?string $model = PointLog::class;

    protected static ?string $navigationLabel = 'Mutasi Poin Member';

    protected static ?string $pluralModelLabel = 'Mutasi Poin Member';

    protected static ?string $modelLabel = 'Mutasi Poin';

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedGift;

    protected static string|UnitEnum|null $navigationGroup = 'Pengguna & Member';

    protected static ?int $navigationSort = 2;

    public static function form(Schema $schema): Schema
    {
        return PointLogForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return PointLogsTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListPointLogs::route('/'),
            'create' => CreatePointLog::route('/create'),
            'edit' => EditPointLog::route('/{record}/edit'),
        ];
    }
}
