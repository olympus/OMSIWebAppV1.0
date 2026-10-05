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
use Filament\Tables\Table;
use UnitEnum;

class PromailerResource extends Resource
{
    protected static ?string $model = Promailer::class;

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-at-symbol';

    protected static string|\UnitEnum|null $navigationGroup = 'Development';

    protected static ?int $navigationSort = 1;

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
