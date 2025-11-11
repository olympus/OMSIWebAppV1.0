<?php

namespace App\Filament\Resources\ProductInfos;

use App\Filament\Resources\ProductInfos\Pages\CreateProductInfo;
use App\Filament\Resources\ProductInfos\Pages\EditProductInfo;
use App\Filament\Resources\ProductInfos\Pages\ListProductInfos;
use App\Filament\Resources\ProductInfos\Pages\ViewProductInfo;
use App\Filament\Resources\ProductInfos\Schemas\ProductInfoForm;
use App\Filament\Resources\ProductInfos\Schemas\ProductInfoInfolist;
use App\Filament\Resources\ProductInfos\Tables\ProductInfosTable;
use App\Models\ProductInfo;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class ProductInfoResource extends Resource
{
    protected static ?string $model = ProductInfo::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    protected static ?string $recordTitleAttribute = 'pd_name';

    public static function form(Schema $schema): Schema
    {
        return ProductInfoForm::configure($schema);
    }

    public static function infolist(Schema $schema): Schema
    {
        return ProductInfoInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return ProductInfosTable::configure($table);
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
            'index' => ListProductInfos::route('/'),
            'create' => CreateProductInfo::route('/create'),
            'view' => ViewProductInfo::route('/{record}'),
            'edit' => EditProductInfo::route('/{record}/edit'),
        ];
    }
}
