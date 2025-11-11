<?php

namespace App\Filament\Resources\Customers\Schemas;

use App\Models\Customers;
use App\Models\Hospitals;
use App\Models\Departments;
use Filament\Infolists\Components\IconEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Schema;

class CustomersInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->components(function (Customers $record) {
            $components = [
                // 🧾 Customer Details
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
                //     ->visible(fn (Customers $record): bool => $record->trashed()),
            ];

            // 🏥 Hospital Details (for View page only)
            $routeName = request()->route()?->getName();

            if (str_contains($routeName, 'customers.view')) {
                $hospitals = Hospitals::where('customer_id', $record->id)->get();

                if ($hospitals->isNotEmpty()) {
                    $count = 1;

                    foreach ($hospitals as $hospital) {
                        $dept_ids = array_filter(explode(',', $hospital->dept_id));
                        $departments = Departments::whereIn('id', $dept_ids)->pluck('name')->all();
                        $depart_names = implode(', ', $departments);

                        //Hospital heading (NO LABEL)
                        $components[] = TextEntry::make("hospital_heading_{$count}")
                            ->label('') // Remove field label completely
                            ->html()
                            ->default("<b style='color:#2b6cb0;'><u>Hospital #{$count}</u></b>")
                            ->columnSpanFull();

                        $components[] = TextEntry::make("hospital_name_{$count}")
                            ->label('Hospital Name')
                            ->default($hospital->hospital_name ?? '-');

                        $components[] = TextEntry::make("departments_{$count}")
                            ->label('Departments')
                            ->default($depart_names ?: '-');

                        $components[] = TextEntry::make("address_{$count}")
                            ->label('Address')
                            ->default($hospital->address ?? '-');

                        $components[] = TextEntry::make("city_{$count}")
                            ->label('City')
                            ->default($hospital->city ?? '-');

                        $components[] = TextEntry::make("state_{$count}")
                            ->label('State')
                            ->default($hospital->state ?? '-');

                        $components[] = TextEntry::make("zip_{$count}")
                            ->label('Pin Code')
                            ->default($hospital->zip ?? '-');

                        $components[] = TextEntry::make("country_{$count}")
                            ->label('Country')
                            ->default($hospital->country ?? '-');

                        $components[] = TextEntry::make("created_on_{$count}")
                            ->label('Created On')
                            ->default(optional($hospital->created_at)->format('Y-m-d H:i'));

                        $components[] = TextEntry::make("updated_on_{$count}")
                            ->label('Last Updated On')
                            ->default(optional($hospital->updated_at)->format('Y-m-d H:i'));

                        $count++;
                    }
                }
            }

            return $components;
        });
    }
}
