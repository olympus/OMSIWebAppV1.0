<?php

namespace App\Filament\Resources\EnquiryMasters;

use App\Filament\Resources\EnquiryMasters\Pages\CreateEnquiryMaster;
use App\Filament\Resources\EnquiryMasters\Pages\EditEnquiryMaster;
use App\Filament\Resources\EnquiryMasters\Pages\ListEnquiryMasters;
use App\Filament\Resources\EnquiryMasters\Pages\ViewEnquiryMaster;
use App\Filament\Resources\EnquiryMasters\Schemas\EnquiryMasterForm;
use App\Filament\Resources\EnquiryMasters\Schemas\EnquiryMasterInfolist;
use App\Filament\Resources\EnquiryMasters\Tables\EnquiryMastersTable;
use App\Models\EnquiryMaster;
use BackedEnum;
use UnitEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class EnquiryMasterResource extends Resource
{
    protected static ?string $model = EnquiryMaster::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    protected static ?string $recordTitleAttribute = 'states';
    protected static string | UnitEnum | null $navigationGroup = 'Auto Emails';
    protected static ?int $navigationSort = 3;
    protected static ?int $navigationGroupSort = 2;
    // protected static string $navigationGroup = 'Auto Emails';

    public static function form(Schema $schema): Schema
    {
        return EnquiryMasterForm::configure($schema);
    }

    public static function infolist(Schema $schema): Schema
    {
        return EnquiryMasterInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return EnquiryMastersTable::configure($table);
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
            'index' => ListEnquiryMasters::route('/'),
            'create' => CreateEnquiryMaster::route('/create'),
            'view' => ViewEnquiryMaster::route('/{record}'),
            'edit' => EditEnquiryMaster::route('/{record}/edit'),
        ];
    }
}
