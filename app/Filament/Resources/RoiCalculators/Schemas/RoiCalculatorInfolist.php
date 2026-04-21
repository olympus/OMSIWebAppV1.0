<?php

namespace App\Filament\Resources\RoiCalculators\Schemas;

use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Schema;

class RoiCalculatorInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextEntry::make('customer.first_name')
                    ->label('Customer Name')
                    ->placeholder('-'),
                TextEntry::make('name')
                    ->placeholder('-'),
                TextEntry::make('email')
                    ->label('Email address')
                    ->placeholder('-'),
                TextEntry::make('mobile')
                    ->placeholder('-'),
                TextEntry::make('hospital_name')
                    ->placeholder('-'),
                TextEntry::make('speciality')
                    ->placeholder('-'),
                TextEntry::make('state')
                    ->placeholder('-'),
                TextEntry::make('city')
                    ->placeholder('-'),
                TextEntry::make('pincode')
                    ->placeholder('-'),
                TextEntry::make('customer_status')
                    ->placeholder('-'),
                TextEntry::make('processor_profile')
                    ->placeholder('-'),
                TextEntry::make('endoscopy_suite')
                    ->placeholder('-'),
                TextEntry::make('procedure_performer')
                    ->placeholder('-'),
                TextEntry::make('procedures_performed')
                    ->placeholder('-'),
                TextEntry::make('created_at')
                    ->dateTime()
                    ->placeholder('-'),
                TextEntry::make('updated_at')
                    ->dateTime()
                    ->placeholder('-'),
            ]);
    }
}
