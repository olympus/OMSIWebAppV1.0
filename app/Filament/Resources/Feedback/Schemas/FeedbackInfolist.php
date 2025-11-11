<?php

namespace App\Filament\Resources\Feedback\Schemas;

use Filament\Infolists\Components\TextEntry;
use Filament\Infolists\Components\Section;
use Filament\Infolists\Components\Grid;
use Filament\Infolists\Infolist;
use Filament\Schemas\Schema;
use App\Models\ServiceRequests;
use App\Models\ArchiveServiceRequests;
use App\Models\Customers;
use App\Models\Hospitals;
use App\Models\Departments;

class FeedbackInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->components([
            // Feedback Details Section
            TextEntry::make('feedback_heading')
                ->default('📝 Feedback Details')
                ->columnSpanFull()
                ->extraAttributes(['style' => 'font-size:18px;font-weight:bold;color:#1f2937;margin-bottom:10px;']),

            TextEntry::make('request_id')
                ->label('Request ID')
                ->badge()
                ->color('primary')
                ->columnSpan(1),

            TextEntry::make('created_at')
                ->label('Submitted At')
                ->dateTime('M j, Y \a\t g:i A')
                ->icon('heroicon-m-calendar-days')
                ->columnSpan(1),

            TextEntry::make('response_speed')
                ->label('Response Speed')
                ->formatStateUsing(fn ($state) => self::renderStars($state))
                ->html()
                ->columnSpan(1),

            TextEntry::make('quality_of_response')
                ->label('Quality of Response')
                ->formatStateUsing(fn ($state) => self::renderStars($state))
                ->html()
                ->columnSpan(1),

            TextEntry::make('app_experience')
                ->label('App Experience')
                ->formatStateUsing(fn ($state) => self::renderStars($state))
                ->html()
                ->columnSpan(1),

            TextEntry::make('olympus_staff_performance')
                ->label('Olympus Staff Performance')
                ->formatStateUsing(fn ($state) => self::renderStars($state))
                ->html()
                ->columnSpan(1),

            TextEntry::make('remarks')
                ->label('Remarks')
                ->columnSpanFull()
                ->placeholder('No remarks provided')
                ->markdown(),

            // Customer Information Section
            TextEntry::make('customer_heading')
                ->default('👤 Customer Information')
                ->columnSpanFull()
                ->extraAttributes(['style' => 'font-size:18px;font-weight:bold;color:#1f2937;margin:20px 0 10px 0;']),

            TextEntry::make('customer_title')
                ->label('Salutation')
                ->default(fn($record) => self::getCustomer($record)?->title ?? '-')
                ->badge()
                ->color('gray')
                ->columnSpan(1),

            TextEntry::make('customer_first_name')
                ->label('First Name')
                ->default(fn($record) => self::getCustomer($record)?->first_name ?? '-')
                ->columnSpan(1),

            TextEntry::make('customer_middle_name')
                ->label('Middle Name')
                ->default(fn($record) => self::getCustomer($record)?->middle_name ?? '-')
                ->columnSpan(1),

            TextEntry::make('customer_last_name')
                ->label('Last Name')
                ->default(fn($record) => self::getCustomer($record)?->last_name ?? '-')
                ->columnSpan(1),

            TextEntry::make('customer_mobile')
                ->label('Mobile Number')
                ->default(fn($record) => self::getCustomer($record)?->mobile_number ?? '-')
                ->icon('heroicon-m-device-phone-mobile')
                ->columnSpan(1),

            TextEntry::make('customer_email')
                ->label('Email Address')
                ->default(fn($record) => self::getCustomer($record)?->email ?? '-')
                ->icon('heroicon-m-envelope')
                ->copyable()
                ->columnSpan(1),

            // Hospital Details Section
            TextEntry::make('hospital_heading')
                ->default('🏥 Hospital Details')
                ->columnSpanFull()
                ->extraAttributes(['style' => 'font-size:18px;font-weight:bold;color:#1f2937;margin:20px 0 10px 0;'])
                ->visible(fn($record) => count(self::getHospitals($record)) > 0),

            ...self::getHospitalEntries(),
        ]);
    }

    private static function getCustomer($record)
    {
        $serviceRequest = ServiceRequests::find($record->request_id)
            ?? ArchiveServiceRequests::find($record->request_id);

        return $serviceRequest ? Customers::find($serviceRequest->customer_id) : null;
    }

    private static function getHospitalEntries(): array
    {
        $entries = [];

        for ($i = 1; $i <= 5; $i++) {
            // Heading for each hospital (only visible if hospital exists)
            $entries[] = TextEntry::make("hospital_{$i}_heading")
                ->default("🏥 Hospital #{$i}")
                ->columnSpanFull()
                ->extraAttributes(['style' => 'font-weight:bold;color:#374151;margin:15px 0 8px 0;font-size:16px;'])
                ->visible(fn($record) => isset(self::getHospitals($record)[$i - 1]));

            $entries[] = TextEntry::make("hospital_{$i}_name")
                ->label('Hospital Name')
                ->default(fn($record) => self::getHospitals($record)[$i - 1]['hospital_name'] ?? '-')
                ->icon('heroicon-m-building-office')
                ->columnSpan(2)
                ->visible(fn($record) => isset(self::getHospitals($record)[$i - 1]));

            $entries[] = TextEntry::make("hospital_{$i}_departments")
                ->label('Departments')
                ->default(fn($record) => self::getHospitals($record)[$i - 1]['departments'] ?? '-')
                ->columnSpan(2)
                ->visible(fn($record) => isset(self::getHospitals($record)[$i - 1]));

            $entries[] = TextEntry::make("hospital_{$i}_address")
                ->label('Address')
                ->default(fn($record) => self::getHospitals($record)[$i - 1]['address'] ?? '-')
                ->icon('heroicon-m-map-pin')
                ->columnSpan(2)
                ->visible(fn($record) => isset(self::getHospitals($record)[$i - 1]));

            $entries[] = TextEntry::make("hospital_{$i}_city")
                ->label('City')
                ->default(fn($record) => self::getHospitals($record)[$i - 1]['city'] ?? '-')
                ->columnSpan(1)
                ->visible(fn($record) => isset(self::getHospitals($record)[$i - 1]));

            $entries[] = TextEntry::make("hospital_{$i}_state")
                ->label('State')
                ->default(fn($record) => self::getHospitals($record)[$i - 1]['state'] ?? '-')
                ->columnSpan(1)
                ->visible(fn($record) => isset(self::getHospitals($record)[$i - 1]));

            $entries[] = TextEntry::make("hospital_{$i}_zip")
                ->label('Pin Code')
                ->default(fn($record) => self::getHospitals($record)[$i - 1]['zip'] ?? '-')
                ->columnSpan(1)
                ->visible(fn($record) => isset(self::getHospitals($record)[$i - 1]));

            $entries[] = TextEntry::make("hospital_{$i}_country")
                ->label('Country')
                ->default(fn($record) => self::getHospitals($record)[$i - 1]['country'] ?? '-')
                ->columnSpan(1)
                ->visible(fn($record) => isset(self::getHospitals($record)[$i - 1]));
        }

        return $entries;
    }

    // ⭐ Render stars for ratings
    protected static function renderStars($state): string
    {
        $stars = '';
        $rating = (int) $state;
        for ($i = 1; $i <= 5; $i++) {
            $stars .= $i <= $rating
                ? '<span style="color:#fbbf24;font-size:18px;">★</span>'
                : '<span style="color:#d1d5db;font-size:18px;">☆</span>';
        }
        return $stars;
    }

    private static function getHospitals($record): array
    {
        $serviceRequest = ServiceRequests::find($record->request_id)
            ?? ArchiveServiceRequests::find($record->request_id);

        $customer = $serviceRequest ? Customers::find($serviceRequest->customer_id) : null;
        if (!$customer) return [];

        $hospitals = Hospitals::where('customer_id', $customer->id)->get();
        if ($hospitals->isEmpty()) return [];

        return $hospitals->map(function ($hospital) {
            $deptIds = explode(',', $hospital->dept_id ?? '');
            $departments = Departments::whereIn('id', $deptIds)->pluck('name')->toArray();

            return [
                'hospital_name' => $hospital->hospital_name,
                'departments'   => implode(', ', $departments),
                'address'       => $hospital->address,
                'city'          => $hospital->city,
                'state'         => $hospital->state,
                'zip'           => $hospital->zip,
                'country'       => $hospital->country,
            ];
        })->toArray();
    }
}
