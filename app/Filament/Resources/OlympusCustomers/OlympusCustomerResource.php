<?php

namespace App\Filament\Resources\OlympusCustomers;

use App\Filament\Resources\OlympusCustomers\Pages\CreateOlympusCustomer;
use App\Filament\Resources\OlympusCustomers\Pages\EditOlympusCustomer;
use App\Filament\Resources\OlympusCustomers\Pages\ListOlympusCustomers;
use App\Filament\Resources\OlympusCustomers\Pages\ViewOlympusCustomer;
use App\Filament\Resources\OlympusCustomers\Schemas\OlympusCustomerForm;
use App\Filament\Resources\OlympusCustomers\Schemas\OlympusCustomerInfolist;
use App\Filament\Resources\OlympusCustomers\Tables\OlympusCustomersTable;
use App\Models\OlympusCustomer;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class OlympusCustomerResource extends Resource
{
    protected static ?string $model = OlympusCustomer::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    protected static ?string $recordTitleAttribute = 'first_name';

    public static function form(Schema $schema): Schema
    {
        return OlympusCustomerForm::configure($schema);
    }

    public static function infolist(Schema $schema): Schema
    {
        return OlympusCustomerInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return OlympusCustomersTable::configure($table);
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
            'index' => ListOlympusCustomers::route('/'),
            'create' => CreateOlympusCustomer::route('/create'),
            'view' => ViewOlympusCustomer::route('/{record}'),
            'edit' => EditOlympusCustomer::route('/{record}/edit'),
        ];
    }

    public static function getRecordRouteBindingEloquentQuery(): Builder
    {
        return parent::getRecordRouteBindingEloquentQuery()
            ->withoutGlobalScopes([
                SoftDeletingScope::class,
            ]);
    }
    
}
