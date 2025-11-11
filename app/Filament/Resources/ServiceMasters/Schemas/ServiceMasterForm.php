<?php

namespace App\Filament\Resources\ServiceMasters\Schemas;

use Filament\Forms\Components\Select;
use Filament\Schemas\Schema;
use App\Models\Departments;
use App\Models\EmployeeTeam;

class ServiceMasterForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                // --- STATES ---
                Select::make('states')
                    ->label('Select States')
                    ->multiple()
                    ->options([
                        'AP' => 'Andhra Pradesh',
                        'AR' => 'Arunachal Pradesh',
                        'AS' => 'Assam',
                        'BR' => 'Bihar',
                        'CT' => 'Chhattisgarh',
                        'GA' => 'Goa',
                        'GJ' => 'Gujarat',
                        'HR' => 'Haryana',
                        'HP' => 'Himachal Pradesh',
                        'JK' => 'Jammu and Kashmir',
                        'JH' => 'Jharkhand',
                        'KA' => 'Karnataka',
                        'KL' => 'Kerala',
                        'MP' => 'Madhya Pradesh',
                        'MH' => 'Maharashtra',
                        'MN' => 'Manipur',
                        'ML' => 'Meghalaya',
                        'MZ' => 'Mizoram',
                        'NL' => 'Nagaland',
                        'OR' => 'Odisha',
                        'PB' => 'Punjab',
                        'RJ' => 'Rajasthan',
                        'SK' => 'Sikkim',
                        'TN' => 'Tamil Nadu',
                        'TS' => 'Telangana',
                        'TR' => 'Tripura',
                        'UK' => 'Uttarakhand',
                        'UP' => 'Uttar Pradesh',
                        'WB' => 'West Bengal',
                        'AN' => 'Andaman and Nicobar Islands',
                        'CH' => 'Chandigarh',
                        'DN' => 'Dadra and Nagar Haveli',
                        'DD' => 'Daman and Diu',
                        'DL' => 'Delhi',
                        'LD' => 'Lakshadweep',
                        'PY' => 'Puducherry',
                    ])
                    ->default([])
                    ->rules(['nullable', 'array'])
                    ->afterStateHydrated(fn($set, $record) =>
                        $set('states', $record && $record->states ? array_filter(explode(',', $record->states), fn($value) => !empty($value)) : [])
                    )
                    ->dehydrateStateUsing(fn($state) => is_array($state) ? implode(',', $state) : null)
                    ->searchable()
                    ->columnSpanFull(),

                // --- SUB TYPE ---
                Select::make('sub_type')
                    ->label('Sub Type')
                    ->options([
                        'capital' => 'Capital',
                        'accessory' => 'Accessory',
                        'other' => 'Other',
                    ])
                    ->required()
                    ->default('capital')
                    ->columnSpan(1),

                // --- DEPARTMENTS ---
                Select::make('departments')
                    ->label('Select Departments')
                    ->multiple()
                    ->options(fn() => Departments::pluck('name', 'id')->toArray())
                    ->default([])
                    ->rules(['nullable', 'array'])
                    ->afterStateHydrated(fn($set, $record) =>
                        $set('departments', $record && $record->departments ? array_filter(explode(',', $record->departments), fn($value) => !empty($value)) : [])
                    )
                    ->dehydrateStateUsing(fn($state) => is_array($state) ? implode(',', $state) : null)
                    ->searchable()
                    ->columnSpanFull(),

                // --- TO EMAILS ---
                Select::make('to_emails')
                    ->label('To Emails')
                    ->multiple()
                    ->options(function () {
                        $emails_sales = EmployeeTeam::where('disabled', '0')->pluck('email', 'email')->toArray();
                        $emails_service = EmployeeTeam::where('disabled', '0')->pluck('email', 'email')->toArray();
                        return [
                            'Sales/Marketing Members' => $emails_sales,
                            'Service Members' => $emails_service,
                        ];
                    })
                    ->default([])
                    ->rules(['nullable', 'array'])
                    ->afterStateHydrated(fn($set, $record) =>
                        $set('to_emails', $record && $record->to_emails ? array_filter(explode(',', $record->to_emails), fn($value) => !empty($value)) : [])
                    )
                    ->dehydrateStateUsing(fn($state) => is_array($state) ? implode(',', $state) : null)
                    ->searchable()
                    ->columnSpanFull(),

                // --- CC EMAILS ---
                Select::make('cc_emails')
                    ->label('CC Emails')
                    ->multiple()
                    ->options(function () {
                        $emails_sales = EmployeeTeam::where('disabled', '0')->pluck('email', 'email')->toArray();
                        $emails_service = EmployeeTeam::where('disabled', '0')->pluck('email', 'email')->toArray();
                        return [
                            'Sales/Marketing Members' => $emails_sales,
                            'Service Members' => $emails_service,
                        ];
                    })
                    ->default([])
                    ->rules(['nullable', 'array'])
                    ->afterStateHydrated(fn($set, $record) =>
                        $set('cc_emails', $record && $record->cc_emails ? array_filter(explode(',', $record->cc_emails), fn($value) => !empty($value)) : [])
                    )
                    ->dehydrateStateUsing(fn($state) => is_array($state) ? implode(',', $state) : null)
                    ->searchable()
                    ->columnSpanFull(),
            ]);
    }
}
