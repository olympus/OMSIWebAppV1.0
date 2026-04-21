<?php

namespace App\Filament\Resources\Categories\Pages;

use App\Filament\Resources\Categories\CategoryResource;
use App\Exports\CategoryMultiSheetExport;
use Maatwebsite\Excel\Facades\Excel;
use Filament\Actions\Action;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListCategories extends ListRecords
{
    protected static string $resource = CategoryResource::class;

    protected function getHeaderActions(): array
    {
        return [
            // Action::make('export')
            //     ->label('Export All')
            //     ->icon('heroicon-o-arrow-down-tray')
            //     ->color('success')
            //     ->action(fn () => Excel::download(new CategoryMultiSheetExport, 'categories.xlsx')),
            CreateAction::make(),
        ];
    }
}
