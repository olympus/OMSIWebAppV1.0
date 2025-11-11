<?php

namespace App\Filament\Resources\EnquiryRequestData\Pages;

use App\Filament\Resources\EnquiryRequestData\EnquiryRequestDataResource;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;

class ViewEnquiryRequestData extends ViewRecord
{
    protected static string $resource = EnquiryRequestDataResource::class;

    protected function getHeaderActions(): array
    {
        return [
            EditAction::make(),
        ];
    }
}
