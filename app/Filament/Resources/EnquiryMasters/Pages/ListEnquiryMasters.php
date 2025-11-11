<?php

namespace App\Filament\Resources\EnquiryMasters\Pages;

use App\Filament\Resources\EnquiryMasters\EnquiryMasterResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListEnquiryMasters extends ListRecords
{
    protected static string $resource = EnquiryMasterResource::class;

    protected function getHeaderActions(): array
    {
        return [
            //CreateAction::make(),
        ];
    }
}
