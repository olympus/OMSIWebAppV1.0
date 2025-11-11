<?php

namespace App\Filament\Resources\EnquiryMasters\Pages;

use App\Filament\Resources\EnquiryMasters\EnquiryMasterResource;
use Filament\Actions\DeleteAction;
use Filament\Actions\ViewAction;
use Filament\Resources\Pages\EditRecord;

class EditEnquiryMaster extends EditRecord
{
    protected static string $resource = EnquiryMasterResource::class;

    protected function getHeaderActions(): array
    {
        return [
            ViewAction::make(),
            DeleteAction::make(),
        ];
    }
}
