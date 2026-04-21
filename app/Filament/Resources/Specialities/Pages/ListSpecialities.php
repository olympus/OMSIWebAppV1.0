<?php

namespace App\Filament\Resources\Specialities\Pages;

use App\Filament\Resources\Specialities\SpecialityResource;
use App\Exports\SpecialityMultiSheetExport;
use Maatwebsite\Excel\Facades\Excel;
use Filament\Actions\Action;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListSpecialities extends ListRecords
{
    protected static string $resource = SpecialityResource::class;

    protected function getHeaderActions(): array
    {
        return [
            // Action::make('export')
            //     ->label('Export All')
            //     ->icon('heroicon-o-arrow-down-tray')
            //     ->color('success')
            //     ->action(fn () => Excel::download(new SpecialityMultiSheetExport, 'specialities.xlsx')),
            CreateAction::make(),
        ];
    }
}
