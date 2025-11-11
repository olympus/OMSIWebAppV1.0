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
class NorthFeedbackChart extends ChartWidget
{   
    use HasFiltersSchema, InteractsWithForms;

    protected ?string $heading = 'Customer Feedback Ratings';

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
        $filtereddata = $homeController->regioncharts('north', $daterange, 'north');

        $departments = array_keys($filtereddata['chart43']);
        $responseSpeed = [];
        $qualityOfResponse = [];
        $appExperience = [];
        $staffPerformance = [];

        foreach ($departments as $dept) {
            if ($dept !== 'Average') {
                $responseSpeed[] = $filtereddata['chart43'][$dept][0] ?? 0;
                $qualityOfResponse[] = $filtereddata['chart43'][$dept][1] ?? 0;
                $appExperience[] = $filtereddata['chart43'][$dept][2] ?? 0;
                $staffPerformance[] = $filtereddata['chart43'][$dept][3] ?? 0;
            }
        }

        return [
            'datasets' => [
                [
                    'label' => 'Response Speed',
                    'data' => $responseSpeed,
                    'backgroundColor' => 'rgba(255, 99, 132, 0.8)',
                ],
                [
                    'label' => 'Quality of Response',
                    'data' => $qualityOfResponse,
                    'backgroundColor' => 'rgba(54, 162, 235, 0.8)',
                ],
                [
                    'label' => 'App Experience',
                    'data' => $appExperience,
                    'backgroundColor' => 'rgba(255, 205, 86, 0.8)',
                ],
                [
                    'label' => 'Staff Performance',
                    'data' => $staffPerformance,
                    'backgroundColor' => 'rgba(75, 192, 192, 0.8)',
                ],
            ],
            'labels' => array_filter($departments, fn($dept) => $dept !== 'Average'),
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
