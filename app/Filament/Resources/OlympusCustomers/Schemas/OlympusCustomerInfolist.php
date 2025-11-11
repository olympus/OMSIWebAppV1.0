<?php

namespace App\Filament\Resources\OlympusCustomers\Schemas;

use App\Models\OlympusCustomer;
use App\Models\Hospitals;
use App\Models\Departments;
use Filament\Infolists\Components\IconEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Illuminate\Support\Collection;

class OlympusCustomerInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Customer Details')
                    ->schema([
                        // TextEntry::make('customer_id')->placeholder('-'),
                        // TextEntry::make('sap_customer_id')->placeholder('-'),
                        // TextEntry::make('title')->placeholder('-'),
                        TextEntry::make('first_name')->placeholder('-'),
                        // TextEntry::make('middle_name')->placeholder('-'),
                        TextEntry::make('last_name')->placeholder('-'),
                        TextEntry::make('mobile_number')->placeholder('-'),
                        TextEntry::make('email')->label('Email address')->placeholder('-'),
                        // TextEntry::make('otp_code')->numeric()->placeholder('-'),
                        // TextEntry::make('valid_upto')->dateTime()->placeholder('-'),
                        IconEntry::make('is_verified')->boolean(),
                        // IconEntry::make('is_testing')->boolean(),
                        // TextEntry::make('platform')->placeholder('-'),
                        // TextEntry::make('app_version')->placeholder('-'),
                        // TextEntry::make('created_at')->dateTime()->placeholder('-'),
                        // TextEntry::make('updated_at')->dateTime()->placeholder('-'),
                        // IconEntry::make('is_expired')->boolean(),
                        // IconEntry::make('is_deleted')->boolean(),
                        // TextEntry::make('deleted_at')
                        //     ->dateTime()
                        //     ->visible(fn (OlympusCustomer $record): bool => $record->trashed()),
                    ]),

                // ✅ Hospital Details Section (view-only)
                Section::make('Hospital Details')
            ->visible(fn () => str_contains(request()->route()?->getName() ?? '', '.view'))
            ->schema([
                TextEntry::make('hospital_details')
                    ->label('')
                    ->getStateUsing(function ($record) {
                        // ✅ Extract hospital IDs
                        $hospitalIds = !empty($record->hospital_id)
                            ? explode(',', $record->hospital_id)
                            : [];

                        if (empty($hospitalIds)) {
                            return '<span style="color: gray;">No hospital linked to this customer.</span>';
                        }

                        // ✅ Get hospitals by IDs
                        $hospitals = \App\Models\Hospitals::whereIn('id', $hospitalIds)->get();

                        if ($hospitals->isEmpty()) {
                            return '<span style="color: gray;">No hospital details found.</span>';
                        }

                        $html = '';
                        $count = 1;

                        foreach ($hospitals as $hospital) {
                            $deptIds = !empty($hospital->dept_id)
                                ? explode(',', $hospital->dept_id)
                                : [];

                            $departments = \App\Models\Departments::whereIn('id', $deptIds)
                                ->pluck('name')
                                ->all();

                            $departNames = implode(', ', $departments);

                            $html .= "
                                <div style='margin-bottom:15px;'>
                                    <b><u>Hospital #{$count}</u></b><br>
                                    <b>Hospital Name:</b> {$hospital->hospital_name}<br>
                                    <b>Departments:</b> {$departNames}<br>
                                    <b>Address:</b> {$hospital->address}<br>
                                    <b>City:</b> {$hospital->city}<br>
                                    <b>State:</b> {$hospital->state}<br>
                                    <b>Pin Code:</b> {$hospital->zip}<br>
                                    <b>Country:</b> {$hospital->country}<br>
                                    <b>Created on:</b> {$hospital->created_at}<br>
                                    <b>Last Updated on:</b> {$hospital->updated_at}<br>
                                </div>
                            ";

                            $count++;
                        }

                        return $html;
                    })
                    ->html(),
                ]),
            ]);
    }
}

