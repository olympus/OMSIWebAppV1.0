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

class EscalationReasonTrendChart extends ChartWidget
{
    use HasFiltersSchema, InteractsWithForms;


    protected ?string $heading = '8. Turnaround Time Trend (Service)';

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


        $direct_requests = DirectRequest::select('id', 'zone', 'created_at')
            ->whereBetween('created_at', [$date_from, $date_to])
            ->get();

        $services_obj = ServiceRequests::where('is_practice', false)
            ->whereBetween('service_requests.created_at', [$date_from, $date_to])
            ->with('hospital')
            ->get();

        foreach ($services_obj as $key) {
            $service_region = find_region($key->hospital->state);
            $key->region = $service_region;
        }

        $period = \Carbon\CarbonPeriod::create($date_from->firstOfMonth(), '1 month', min($date_to, now()));
        foreach ($period as $dt) {
            $chart_months[] = $dt->format("M-y");
            $months12[] = $dt->format("m");
            $year = $dt->format("Y");
            $year_of = ($dt->format("n") > 3) ? $year : $year-1;
            $years[$year_of][] = $dt->format("M-y");
        }
        $chart_months = array_reverse($chart_months);
        $months12 = array_reverse($months12);
        $months_count = count($months12);
        $date_to1 = new Carbon(explode("_", $daterange)[1]);


        $chart93 = \App\Charts\GetCharts::chart93($daterange, $services_obj, $months12, $chart_months, $date_to1);

        $datasets = [];
        $labels = array_reverse($chart_months);

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

        $index = 0;
        foreach ($chart93 as $reason => $data) {
            $datasets[] = [
                'label' => $reason,
                'data' => array_reverse($data),
                'backgroundColor' => $colors[$index % count($colors)],
                'borderColor' => $borderColors[$index % count($borderColors)],
                'borderWidth' => 2,
            ];
            $index++;
        }

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
                    'stacked' => true,
                    'title' => [
                        'display' => true,
                        'text' => 'Months'
                    ]
                ],
                'y' => [
                    'stacked' => true,
                    'title' => [
                        'display' => true,
                        'text' => 'Requests'
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


