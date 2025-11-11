<?php

namespace App\Filament\Widgets;

use Filament\Widgets\ChartWidget;
use App\Models\Departments;
use App\Models\DirectRequest;
use App\Models\ServiceRequests;
use Carbon\Carbon;
use Carbon\CarbonPeriod;

use Filament\Forms\Components\DatePicker;
use Filament\Schemas\Schema;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Widgets\ChartWidget\Concerns\HasFiltersSchema;

class AverageTatReportChart extends ChartWidget
{
    use HasFiltersSchema, InteractsWithForms;


    protected ?string $heading = '7. Average TAT Report (Service)';

    protected int | string | array $columnSpan = 'full';

    protected ?string $maxHeight = '1000px';

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
        $formData = $this->form->getState();
        $date_from = $this->filters['startDate'] ?? $this->getDefaultFromDate();
        $date_to = $this->filters['endDate'] ?? $this->getDefaultToDate();

        $daterange = $date_from . '_' . $date_to;
        $date_from = new Carbon($date_from);
        $date_to = new Carbon($date_to);




        $services_obj = ServiceRequests::where('is_practice', false)
            ->whereBetween('service_requests.created_at', [$date_from, $date_to])
            ->with('hospital')
            ->get();

        $chart91 = \App\Charts\GetCharts::chart91($services_obj);

        $datasets = [];
        $labels = [
            'Delayed Action from Olympus',
            'I did not get enough information',
            'No Response from Olympus',
            'Other'
        ];

        $colors = [
            'rgba(255, 99, 132, 0.2)', // Red
            'rgba(54, 162, 235, 0.2)', // Blue
            'rgba(255, 205, 86, 0.2)', // Yellow
            'rgba(75, 192, 192, 0.2)', // Green
        ];

        $borderColors = [
            'rgba(255, 99, 132, 1)',
            'rgba(54, 162, 235, 1)',
            'rgba(255, 205, 86, 1)',
            'rgba(75, 192, 192, 1)',
        ];

        $datasets[] = [
            'label' => 'Escalation Reasons',
            'data' => $chart91,
            'backgroundColor' => $colors,
            'borderColor' => $borderColors,
            'borderWidth' => 2,
        ];

        return [
            'datasets' => $datasets,
            'labels' => $labels,
        ];
    }

    protected ?array $options = [
        'plugins' => [
            'legend' => [
                'display' => false,
            ],
        ],
    ];

    protected function getOptions(): array
    {
        return [
            'plugins' => [
                'legend' => [
                    'display' => true,
                ],
                'datalabels' => [
                    'anchor' => 'end',
                    'align' => 'end',
                    'color' => '#000',
                    'font' => [
                        'weight' => 'bold',
                    ],
                ],
            ],
            'scales' => [
                'x' => [
                    'title' => [
                        'display' => true,
                        'text' => 'Escalation Reasons'
                    ]
                ],
                'y' => [
                    'title' => [
                        'display' => true,
                        'text' => 'Count'
                    ],
                    'ticks' => [
                        'stepSize' => 1,
                        'min' => 0,
                    ],
                ],
            ],
            'responsive' => true,
            'maintainAspectRatio' => false,
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

