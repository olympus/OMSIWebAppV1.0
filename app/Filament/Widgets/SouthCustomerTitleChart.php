<?php

namespace App\Filament\Widgets;

use Filament\Widgets\ChartWidget;
use Filament\Widgets\BarChartWidget;

use App\Http\Controllers\HomeController;

use Carbon\Carbon;
use Carbon\CarbonPeriod;

use Filament\Forms\Components\DatePicker;
use Filament\Schemas\Schema;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Widgets\ChartWidget\Concerns\HasFiltersSchema;

class SouthCustomerTitleChart extends BarChartWidget
{
    use HasFiltersSchema, InteractsWithForms;

    protected ?string $heading = 'Customer Titles';

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



        $controller = app(HomeController::class);
        $daterange = $date_from . '_' . $date_to;

        $filteredData = $controller->regioncharts('south', $daterange, 'south');
        $chartData = $filteredData['chart37'] ?? [];

        return [
            'labels' => array_keys($chartData),
            'datasets' => [
                [
                    'label' => 'Titles',
                    'data' => array_values($chartData),
                ],
            ],
        ];
    }

    protected function getType(): string
    {
        return 'doughnut';
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
