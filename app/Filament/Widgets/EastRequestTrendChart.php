<?php

namespace App\Filament\Widgets;

use App\Http\Controllers\HomeController;
use Filament\Widgets\ChartWidget;

use Carbon\Carbon;
use Carbon\CarbonPeriod;

use Filament\Forms\Components\DatePicker;
use Filament\Schemas\Schema;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Widgets\ChartWidget\Concerns\HasFiltersSchema;

class EastRequestTrendChart extends ChartWidget
{
    use HasFiltersSchema, InteractsWithForms;

    protected ?string $heading = 'Request Trends Over Time';

    protected bool $isCollapsible = true;

    public function filtersSchema(Schema $schema): Schema
    {
        return $schema->components([
            DatePicker::make('startDate'),
            DatePicker::make('endDate'),
        ]);
    }

    protected function getData(): array
    {
        $date_from = $this->filters['startDate'] ?? $this->getDefaultFromDate();
        $date_to = $this->filters['endDate'] ?? $this->getDefaultToDate();

        $daterange = $date_from . '_' . $date_to;
        $date_from = new Carbon($date_from);
        $date_to = new Carbon($date_to);


        $homeController = new HomeController();
        $filtereddata = $homeController->regioncharts('east', $daterange, 'east');

        return [
            'datasets' => [
                [
                    'label' => 'Service',
                    'data' => $filtereddata['chart33']['Service'] ?? [],
                    'borderColor' => 'rgba(54, 162, 235, 1)',
                    'backgroundColor' => 'rgba(54, 162, 235, 0.1)',
                ],
                [
                    'label' => 'Academic',
                    'data' => $filtereddata['chart33']['Academic'] ?? [],
                    'borderColor' => 'rgba(255, 205, 86, 1)',
                    'backgroundColor' => 'rgba(255, 205, 86, 0.1)',
                ],
                [
                    'label' => 'Enquiry',
                    'data' => $filtereddata['chart33']['Enquiry'] ?? [],
                    'borderColor' => 'rgba(75, 192, 192, 1)',
                    'backgroundColor' => 'rgba(75, 192, 192, 0.1)',
                ],
            ],
            'labels' => $filtereddata['chart_months'] ?? [],
        ];
    }

    protected function getType(): string
    {
        return 'line';
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
