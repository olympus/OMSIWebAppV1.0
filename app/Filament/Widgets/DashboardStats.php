<?php

namespace App\Filament\Widgets;

use App\Models\Customers;
use App\Models\ServiceRequests;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class DashboardStats extends BaseWidget
{
    protected function getStats(): array
    {
        $pending_service_requests_count = ServiceRequests::where('is_practice', false)->where('request_type', 'service')->where('status', 'Received')->count();
        $pending_enquiry_requests_count = ServiceRequests::where('is_practice', false)->where('request_type', 'enquiry')->where('status', 'Received')->count();
        $pending_academic_requests_count = ServiceRequests::where('is_practice', false)->where('request_type', 'academic')->where('status', 'Received')->count();
        $new_customers = Customers::count();
        return [
            Stat::make('New Service Requests', $pending_service_requests_count)
                ->description('Total pending services')
                ->descriptionIcon('heroicon-m-cog-6-tooth')
                ->color('primary') // blue
                ->chart([5, 10, 15, 20, 17, 30, 40]),

            Stat::make('New Enquiry Requests', $pending_enquiry_requests_count)
                ->description('Latest enquiries')
                ->descriptionIcon('heroicon-m-question-mark-circle')
                ->color('success') // green
                ->chart([1, 4, 6, 8, 10, 9, 12]),

            Stat::make('New Academic Requests',$pending_academic_requests_count)
                ->description('This month')
                ->descriptionIcon('heroicon-m-book-open')
                ->color('warning') // yellow
                ->chart([2, 3, 5, 7, 11, 13, 15]),

            Stat::make('User Registrations', $new_customers)
                ->description('Total registered users')
                ->descriptionIcon('heroicon-m-user-group')
                ->color('danger') // red
                ->chart([10, 15, 20, 25, 30, 35, 40]),
        ];
    }
}
