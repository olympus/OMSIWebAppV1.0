<?php

namespace App\Filament\Widgets;

use App\Http\Controllers\HomeController;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

use Carbon\Carbon;
use Carbon\CarbonPeriod;

use Filament\Forms\Components\DatePicker;
use Filament\Schemas\Schema;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Widgets\ChartWidget\Concerns\HasFiltersSchema;

class SouthDashboardStats extends BaseWidget
{
    use HasFiltersSchema, InteractsWithForms;

    protected bool $isCollapsible = true;

    public function filtersSchema(Schema $schema): Schema
    {
        return $schema->components([
            DatePicker::make('startDate'),
            DatePicker::make('endDate'),
        ]);
    }

    protected function getStats(): array
    {
        $date_from = $this->filters['startDate'] ?? $this->getDefaultFromDate();
        $date_to = $this->filters['endDate'] ?? $this->getDefaultToDate();

        $daterange = $date_from . '_' . $date_to;
        $date_from = new Carbon($date_from);
        $date_to = new Carbon($date_to);

        $homeController = new HomeController();
        $filtereddata = $homeController->regioncharts('south', $daterange, 'south');

        return [
            Stat::make('Pending Service Requests', $filtereddata['pending_service_requests_count'])
                ->description('New service requests')
                ->descriptionIcon('heroicon-m-arrow-trending-up')
                ->color('success'),
            Stat::make('Pending Enquiry Requests', $filtereddata['pending_enquiry_requests_count'])
                ->description('New enquiry requests')
                ->descriptionIcon('heroicon-m-arrow-trending-up')
                ->color('warning'),
            Stat::make('Pending Academic Requests', $filtereddata['pending_academic_requests_count'])
                ->description('New academic requests')
                ->descriptionIcon('heroicon-m-arrow-trending-up')
                ->color('info'),
            Stat::make('Total Customers', $filtereddata['new_customers'])
                ->description('User registrations')
                ->descriptionIcon('heroicon-m-arrow-trending-up')
                ->color('danger'),
        ];
    }

    private function getDefaultFromDate(): string
    {
        $month = date('m');
        if ($month >= 4) {
            $y = date('Y');
        } else {
            $y = date('Y', strtotime('-1 year'));
        }
        return $y . "-04-01";
    }

    private function getDefaultToDate(): string
    {
        $month = date('m');
        if ($month >= 4) {
            $pt = date('Y', strtotime('+1 year'));
        } else {
            $pt = date('Y');
        }
        return $pt . "-03-31";
    }
}
