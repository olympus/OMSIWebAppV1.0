<?php

namespace App\Filament\Resources\EnquiryMasters\Pages;

use App\Filament\Resources\EnquiryMasters\EnquiryMasterResource;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;

class ViewEnquiryMaster extends ViewRecord
{
    protected static string $resource = EnquiryMasterResource::class;

    protected function getHeaderActions(): array
    {
        return [
            EditAction::make(),
        ];
    }
}
