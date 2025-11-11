<?php

namespace App\Filament\Resources\EnquiryRequestData\Pages;

use App\Filament\Resources\EnquiryRequestData\EnquiryRequestDataResource;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Database\Eloquent\Builder;

class ListEnquiryRequestData extends ListRecords
{
    protected static string $resource = EnquiryRequestDataResource::class;

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

        // Apply filters: status from URL + request_type = 'enquiry'
        $query->where('request_type', 'enquiry');

        if ($status = request()->get('status')) {
            $query->where('status', $status);
        }

        return $query;
    }

    public function getTitle(): string
    {
        return 'Enquiry Requests - ' . (request('status') ?? 'All');
    }
}
