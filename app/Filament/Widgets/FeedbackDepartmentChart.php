<?php

namespace App\Filament\Widgets;

use Filament\Widgets\ChartWidget;
use App\Models\Departments;
use App\Models\ServiceRequests;
use Carbon\Carbon;
use Carbon\CarbonPeriod;

use Filament\Forms\Components\DatePicker;
use Filament\Schemas\Schema;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Widgets\ChartWidget\Concerns\HasFiltersSchema;

class FeedbackDepartmentChart extends ChartWidget
{
    use HasFiltersSchema, InteractsWithForms;


    protected ?string $heading = '3. Feedback on service request (Department)';

    //protected int | string | array $columnSpan = 'full';

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

        foreach ($services_obj as $key) {
            $service_region = find_region($key->hospital->state);
            $key->region = $service_region;
        }

        $dept_obj = Departments::get();
        $period = CarbonPeriod::create($date_from->firstOfMonth(), '1 month', $date_to);
        foreach ($period as $dt) {
            $chart_months[] = $dt->format("M-y");
            $months12[] = $dt->format("m");
            $year = $dt->format("Y");
            $year_of = ($dt->format("n") > 3) ? $year : $year - 1;
            $years[$year_of][] = $dt->format("M-y");
        }

        $chart71 = \App\Charts\GetCharts::chart71($daterange, $services_obj, $dept_obj);

        $datasets = [];
        $labels = [
            '1.Response Speed',
            '2.Quality of Response',
            '3.App Experience',
            '4.Performance of Olympus Employee'
        ];
        $maxValue = 5;

        $colors = [
            'rgba(255, 99, 132, 0.2)', // Red
            'rgba(54, 162, 235, 0.2)', // Blue
            'rgba(255, 205, 86, 0.2)', // Yellow
            'rgba(75, 192, 192, 0.2)', // Green
            'rgba(153, 102, 255, 0.2)', // Purple
            'rgba(255, 159, 64, 0.2)', // Orange
            'rgba(199, 199, 199, 0.2)', // Grey
            'rgba(83, 102, 255, 0.2)', // Indigo
            'rgba(255, 99, 255, 0.2)', // Pink
        ];

        $borderColors = [
            'rgba(255, 99, 132, 1)',
            'rgba(54, 162, 235, 1)',
            'rgba(255, 205, 86, 1)',
            'rgba(75, 192, 192, 1)',
            'rgba(153, 102, 255, 1)',
            'rgba(255, 159, 64, 1)',
            'rgba(199, 199, 199, 1)',
            'rgba(83, 102, 255, 1)',
            'rgba(255, 99, 255, 1)',
        ];

        $index = 0;
        foreach ($chart71 as $dept => $data) {
            $datasets[] = [
                'label' => $dept,
                'data' => $data,
                'backgroundColor' => $colors[$index % count($colors)],
                'borderColor' => $borderColors[$index % count($borderColors)],
                'borderWidth' => 2,
            ];
            $index++;
        }

        return [
            'datasets' => $datasets,
            'labels' => $labels,
            'maxValue' => $maxValue,
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
        $data = $this->getData();
        $maxValue = $data['maxValue'] ?? 15;

        // Round up to nearest multiple of 5
        $roundedMax = ceil($maxValue / 5) * 5;

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
                        'text' => 'Feedback Categories'
                    ]
                ],
                'y' => [
                    'stacked' => true,
                    'title' => [
                        'display' => true,
                        'text' => 'Feedback Rating'
                    ],
                    'ticks' => [
                        'stepSize' => 1,
                        'min' => 0,
                        'max' => $roundedMax
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
