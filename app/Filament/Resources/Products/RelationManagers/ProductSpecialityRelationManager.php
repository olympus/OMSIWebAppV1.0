<?php

namespace App\Filament\Resources\Products\RelationManagers;

use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables\Table;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ToggleColumn;
use Filament\Actions\CreateAction;
use Filament\Actions\EditAction;
use Filament\Actions\DeleteAction;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;
use Illuminate\Validation\Rule;
use App\Models\Speciality;
use App\Models\ProductSpeciality;

class ProductSpecialityRelationManager extends RelationManager
{
    protected static string $relationship = 'productSpecialities';

    public function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('speciality.specialities_name')
                    ->label('Speciality')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('subSpeciality.specialities_name')
                    ->label('Sub Speciality')
                    ->searchable()
                    ->sortable(),

                ToggleColumn::make('status'),

                TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable(),
            ])
            ->filters([
                // add filters if needed
            ])
            ->defaultSort('id','desc')
            ->headerActions([
                CreateAction::make()->slideOver(),
            ])
            ->recordActions([
                EditAction::make(),
                DeleteAction::make(),
            ]);
    }

    public function form(Schema $schema): Schema
    {
        return $schema->components([

            Select::make('speciality_id')
                ->label('Select Speciality')
                ->options(
                    Speciality::whereNull('parent_id')
                        ->where('status', 1)
                        ->pluck('specialities_name', 'id')
                )
                ->searchable()
                ->required()
                ->reactive()
                ->afterStateUpdated(fn (callable $set) => $set('sub_speciality_id', null)),

            Select::make('sub_speciality_id')
                ->label('Select Sub Speciality')
                ->options(function ($get) {
                    return Speciality::where('parent_id', $get('speciality_id'))
                        ->where('status', 1)
                        ->pluck('specialities_name', 'id');
                })
                ->searchable()
                ->required()
                ->rule(function ($get, $record) {
                    return function (string $attribute, $value, \Closure $fail) use ($get, $record) {

                        $exists = ProductSpeciality::where('product_id', $this->getOwnerRecord()->id)
                            ->where('speciality_id', $get('speciality_id'))
                            ->where('sub_speciality_id', $value)
                            ->whereNULL('deleted_at')
                            ->when($record, fn ($q) => $q->where('id', '!=', $record->id))
                            ->exists();

                        if ($exists) {
                            $fail('This speciality and sub speciality combination is already assigned to this product.');
                        }
                    };
                }),
            // Select::make('sub_speciality_id')
            //     ->label('Select Sub Speciality')
            //     ->options(function ($get) {
            //         return Speciality::where('parent_id', $get('speciality_id'))
            //             ->where('status', 1)
            //             ->pluck('specialities_name', 'id');
            //     })
            //     ->searchable()
            //     ->required()

            //     ->rules([
            //         fn ($get, $record): \Closure => function (string $attribute, $value, \Closure $fail) use ($get, $record) {
            //             $exists = \DB::table('product_specialities')
            //                 ->where('product_id', $this->getOwnerRecord()->id)
            //                 ->where('speciality_id', $get('speciality_id'))
            //                 ->where('sub_speciality_id', $value)
            //                 ->when($record, fn($q) => $q->where('id', '!=', $record->id))
            //                 ->exists();

            //             if ($exists) {
            //                 $fail('This speciality and sub speciality combination is already assigned to this product.');
            //             }
            //         },
            //     ]),

            Toggle::make('status')
                ->default(true),
        ]);
    }

    /*protected function getFormValidationRules(): array
    {
        return [
            'speciality_id' => [
                'required',
                Rule::unique('product_specialities')
                    ->where(function ($query) {
                        return $query
                            ->where('product_id', $this->ownerRecord->id)
                            ->where('sub_speciality_id', request()->input('sub_speciality_id'))
                            ->whereNull('deleted_at'); // ✅ ADD THIS
                    })
                    ->ignore($this->record),
                // Rule::unique('product_specialities')
                //     ->where(function ($query) {
                //         return $query
                //             ->where('product_id', $this->ownerRecord->id)
                //             ->where('sub_speciality_id', request()->input('sub_speciality_id'));
                //     })
                //     ->ignore($this->record),
            ],
        ];
    }*/

    protected function getFormValidationMessages(): array
    {
        return [
            'speciality_id.unique' => 'This speciality and sub speciality combination already exists for this product.',
        ];
    }
}
