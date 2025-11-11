<?php

namespace App\Filament\Resources\Customers;

use App\Filament\Resources\Customers\Pages\CreateCustomers;
use App\Filament\Resources\Customers\Pages\EditCustomers;
use App\Filament\Resources\Customers\Pages\ListCustomers;
use App\Filament\Resources\Customers\Pages\ViewCustomers;
use App\Filament\Resources\Customers\Schemas\CustomersForm;
use App\Filament\Resources\Customers\Schemas\CustomersInfolist;
use App\Filament\Resources\Customers\Tables\CustomersTable;
use App\Models\Customers;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class CustomersResource extends Resource
{
    protected static ?string $model = Customers::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;
    protected static ?int $navigationSort = 2;

    protected static ?string $recordTitleAttribute = 'first_name';
    protected static ?int $navigationGroupSort = 4;

    public static function form(Schema $schema): Schema
    {
        return CustomersForm::configure($schema);
    }

    public static function infolist(Schema $schema): Schema
    {
        return CustomersInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return CustomersTable::configure($table);
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
            'index' => ListCustomers::route('/'),
            'create' => CreateCustomers::route('/create'),
            'view' => ViewCustomers::route('/{record}'),
            'edit' => EditCustomers::route('/{record}/edit'),
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
