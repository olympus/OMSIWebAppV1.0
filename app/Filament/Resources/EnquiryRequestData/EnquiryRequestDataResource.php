<?php

namespace App\Filament\Resources\EnquiryRequestData;

use App\Filament\Resources\EnquiryRequestData\Pages\CreateEnquiryRequestData;
use App\Filament\Resources\EnquiryRequestData\Pages\EditEnquiryRequestData;
use App\Filament\Resources\EnquiryRequestData\Pages\ListEnquiryRequestData;
use App\Filament\Resources\EnquiryRequestData\Pages\ViewEnquiryRequestData; 
use App\Filament\Resources\EnquiryRequestData\Schemas\EnquiryRequestDataForm;
use App\Filament\Resources\EnquiryRequestData\Tables\EnquiryRequestDataTable;
use App\Filament\Resources\EnquiryRequestData\Schemas\EnquiryRequestDataInfolist;

use App\Models\ServiceRequests;
use App\Models\ArchiveServiceRequests;
use App\Models\CombinedServiceRequests;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Filament\Navigation\NavigationItem;
use Filament\Support\Colors\Color;

class EnquiryRequestDataResource extends Resource
{
    protected static ?string $model = CombinedServiceRequests::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    /**
     * ✅ Dynamic side navigation — counts from BOTH tables.
     */
    public static function getNavigationItems(): array
    {
        $statuses = [
            'Received',
            'Assigned', 
            'Attended', 
            'Closed',
        ];

        $items = [];

        foreach ($statuses as $status) {
            if($status == 'Received'){
                $show_status = "Received";
                $status = "Received";
            }elseif($status == 'Assigned'){
                $show_status = "Assigned";
                $status = "Assigned";
            }
            // elseif($status == 'Re Assigned'){
            //     $show_status = "Re Assigned";
            //     $status = "Re-assigned";
            // }
            elseif($status == 'Attended'){
                $show_status = "Attended";
                $status = "Attended";
            }
            // elseif($status == 'Received At Repair Center'){
            //     $show_status = "Received At Repair Center";
            //     $status = "Received_At_Repair_Center";
            // }elseif($status == 'Quotation Prepared'){
            //     $show_status = "Quotation Prepared";
            //     $status = "Quotation_Prepared";
            // }elseif($status == 'PO Received'){
            //     $show_status = "PO Received";
            //     $status = "PO_Received";
            // }elseif($status == 'Repair Started'){
            //     $show_status = "Repair Started";
            //     $status = "Repair_Started";
            // }elseif($status == 'Repair Completed'){
            //     $show_status = "Repair Completed";
            //     $status = "Repair_Completed";
            // }elseif($status == 'Ready To Dispatch'){
            //     $show_status = "Ready To Dispatch";
            //     $status = "Ready_To_Dispatch";
            // }elseif($status == 'Dispatched'){
            //     $show_status = "Dispatched";
            //     $status = "Dispatched";
            // }
            elseif($status == 'Closed'){
                $show_status = "Closed";
                $status = "Closed";
            } 

            $activeCount = ServiceRequests::where('request_type', 'like', '%enquiry%')
                ->when($status, function ($q) use ($status) {
                    $q->where('status', $status);
                })->count();

            //where('status', $status)->count();
            $archiveCount = ArchiveServiceRequests::where('request_type', 'like', '%enquiry%')
                ->when($status, function ($q) use ($status) {
                    $q->where('status', $status);
                })->count();
                //where('status', $status)->count();
            $totalCount = $activeCount + $archiveCount;

            $items[] = NavigationItem::make("{$show_status} ({$totalCount})")
                ->icon('heroicon-o-clipboard-document-list')
                ->group('Enquiry Requests')
                ->url(static::getUrl('index', ['status' => $status]))
                ->badge($totalCount ?: null);
        }

        return $items;
    }

    public static function form(Schema $schema): Schema
    {
        return EnquiryRequestDataForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return EnquiryRequestDataTable::configure($table);
    }


    public static function infolist(Schema $schema): Schema
    {
        return EnquiryRequestDataInfolist::configure($schema);
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListEnquiryRequestData::route('/'),
            'view' => ViewEnquiryRequestData::route('/{record}'),
            'create' => CreateEnquiryRequestData::route('/create'),
            'edit' => EditEnquiryRequestData::route('/{record}/edit'),
        ];
    }
}
