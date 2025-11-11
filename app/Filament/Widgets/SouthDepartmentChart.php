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

class SouthDepartmentChart extends ChartWidget
{
    use HasFiltersSchema, InteractsWithForms;

    protected ?string $heading = 'Requests by Department';

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
        $filtereddata = $homeController->regioncharts('south', $daterange, 'south');

        $departments = ['Service', 'Enquiry', 'Academic'];
        $data = [];

        foreach ($departments as $dept) {
            $data[] = $filtereddata['chart32'][$dept];
        }

        return [
            'datasets' => [
                [
                    'label' => 'Service',
                    'data' => $data[0],
                    'backgroundColor' => 'rgba(54, 162, 235, 0.8)',
                ],
                [
                    'label' => 'Enquiry',
                    'data' => $data[1],
                    'backgroundColor' => 'rgba(255, 205, 86, 0.8)',
                ],
                [
                    'label' => 'Academic',
                    'data' => $data[2],
                    'backgroundColor' => 'rgba(75, 192, 192, 0.8)',
                ],
            ],
            'labels' => ['Gastroenterology', 'Respiratory', 'General Surgery', 'Urology', 'Gynaecology', 'ENT', 'Others', 'BioMedical'],
        ];
    }

    protected function getType(): string
    {
        return 'bar';
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
