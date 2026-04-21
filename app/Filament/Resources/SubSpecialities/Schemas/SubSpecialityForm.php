<?php

namespace App\Filament\Resources\SubSpecialities\Schemas;

use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Select;
use App\Models\Speciality;
use Filament\Schemas\Schema;

class SubSpecialityForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('speciality_id')
                    ->label('Speciality')
                    ->options(function () {
                        return Speciality::orderBy('specialities_name', 'ASC')
                            ->get()
                            ->mapWithKeys(fn ($s) => [$s->id => $s->specialities_name])
                            ->toArray();
                    })
                    ->searchable()
                    ->preload()
                    ->required()
                    ->default(function ($record) {
                        return $record ? $record->speciality_id : null;
                    }),
                TextInput::make('sub_specialities_name')
                    ->default(null),
                FileUpload::make('sub_specialities_image')
                    ->image(),
                Textarea::make('sub_specialities_description')
                    ->default(null)
                    ->columnSpanFull(),
                Toggle::make('status')
                    ->default(1)
                    ->required(),
                TextInput::make('orderby')
                    ->numeric()
                    ->default(null),
                Hidden::make('created_by')
                    ->default(function ($record) {
                        return $record ? $record->created_by : auth()->id();
                    }),
            ]);
    }
}
