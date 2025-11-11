<?php

namespace App\Filament\Resources\Promailers;

use App\Filament\Resources\Promailers\Pages\CreatePromailer;
use App\Filament\Resources\Promailers\Pages\EditPromailer;
use App\Filament\Resources\Promailers\Pages\ListPromailers;
use App\Filament\Resources\Promailers\Schemas\PromailerForm;
use App\Filament\Resources\Promailers\Tables\PromailersTable;
use App\Models\Promailer;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class PromailerResource extends Resource
{
    protected static ?string $model = Promailer::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    public static function form(Schema $schema): Schema
    {
        return PromailerForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return PromailersTable::configure($table);
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
            'index' => ListPromailers::route('/'),
            'create' => CreatePromailer::route('/create'),
            'edit' => EditPromailer::route('/{record}/edit'),
        ];
    }
}
