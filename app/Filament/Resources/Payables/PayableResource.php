<?php

namespace App\Filament\Resources\Payables;

use App\Filament\Resources\Payables\Pages\CreatePayable;
use App\Filament\Resources\Payables\Pages\EditPayable;
use App\Filament\Resources\Payables\Pages\ListPayables;
use App\Filament\Resources\Payables\Schemas\PayableForm;
use App\Filament\Resources\Payables\Tables\PayablesTable;
use App\Models\Payable;
use BackedEnum;
use UnitEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use App\Filament\Resources\Payables\RelationManagers\PaymentsRelationManager;


class PayableResource extends Resource
{   
    protected static ?string $label = 'Hutang';
    protected static ?string $pluralLabel = 'Daftar Hutang';
    protected static ?string $model = Payable::class;
    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedArrowUpCircle;
    protected static ?string $recordTitleAttribute = 'invoice_number';
    protected static ?string $navigationLabel = 'Hutang';
    protected static string|UnitEnum|null $navigationGroup = 'Transaksi';
    protected static ?int $navigationSort = 2;

    public static function form(Schema $schema): Schema
    {
        return PayableForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return PayablesTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [
            PaymentsRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index'  => ListPayables::route('/'),
            'create' => CreatePayable::route('/create'),
            'edit'   => EditPayable::route('/{record}/edit'),
        ];
    }
}