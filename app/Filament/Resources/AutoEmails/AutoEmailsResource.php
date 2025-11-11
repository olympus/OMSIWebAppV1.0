<?php

namespace App\Filament\Resources\AutoEmails;

use App\Filament\Resources\AutoEmails\Pages\CreateAutoEmails;
use App\Filament\Resources\AutoEmails\Pages\EditAutoEmails;
use App\Filament\Resources\AutoEmails\Pages\ListAutoEmails;
use App\Filament\Resources\AutoEmails\Pages\ViewAutoEmails;
use App\Filament\Resources\AutoEmails\Schemas\AutoEmailsForm;
use App\Filament\Resources\AutoEmails\Schemas\AutoEmailsInfolist;
use App\Filament\Resources\AutoEmails\Tables\AutoEmailsTable;
use App\Models\AutoEmails;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class AutoEmailsResource extends Resource
{
    protected static ?string $model = AutoEmails::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;
    protected static ?int $navigationSort = 4;

    protected static ?string $recordTitleAttribute = 'states';
    protected static ?int $navigationGroupSort = 2;

    public static function form(Schema $schema): Schema
    {
        return AutoEmailsForm::configure($schema);
    }

    public static function infolist(Schema $schema): Schema
    {
        return AutoEmailsInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return AutoEmailsTable::configure($table);
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
            'index' => ListAutoEmails::route('/'),
            'create' => CreateAutoEmails::route('/create'),
            'view' => ViewAutoEmails::route('/{record}'),
            'edit' => EditAutoEmails::route('/{record}/edit'),
        ];
    }
}
