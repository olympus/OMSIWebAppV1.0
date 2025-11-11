<?php

namespace App\Filament\Resources\AcademicRequestData\Pages;

use App\Filament\Resources\AcademicRequestData\AcademicRequestDataResource;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Database\Eloquent\Builder;

class ListAcademicRequestData extends ListRecords
{
    protected static string $resource = AcademicRequestDataResource::class;

    // protected function getTableQuery(): Builder
    // {
    //     $query = parent::getTableQuery(); 
    //     if ($status = request()->get('status')) {
    //         $query->where('status', $status);
    //     }

    //     return $query;
    // }

    protected function getTableQuery(): Builder
    {
        $query = parent::getTableQuery();

        // Apply filters: status from URL + request_type = 'academic'
        $query->where('request_type', 'academic');

        if ($status = request()->get('status')) {
            $query->where('status', $status);
        }

        return $query;
    }

    public function getTitle(): string
    {
        return 'Academic Requests - ' . (request('status') ?? 'All');
    }
}
