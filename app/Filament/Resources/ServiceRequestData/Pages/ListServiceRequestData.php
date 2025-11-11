<?php

namespace App\Filament\Resources\ServiceRequestData\Pages;

use App\Filament\Resources\ServiceRequestData\ServiceRequestDataResource;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Database\Eloquent\Builder;

class ListServiceRequestData extends ListRecords
{
    protected static string $resource = ServiceRequestDataResource::class;

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

        // Apply filters: status from URL + request_type = 'service'
        $query->where('request_type', 'service');

        if ($status = request()->get('status')) {
            $query->where('status', $status);
        }

        return $query;
    }

    public function getTitle(): string
    {
        return 'Service Requests - ' . (request('status') ?? 'All');
    }
}
