<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\WithMultipleSheets;
use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithTitle;
class CategorySampleExport implements WithMultipleSheets
{
    public function sheets(): array
    {
        return [
            new CategorySampleSheet(),
            new SubCategorySampleSheet(),
        ];
    }
}

class CategorySampleSheet implements FromArray, WithHeadings, WithTitle
{
    public function title(): string
    {
        return 'Categories';
    }

    public function headings(): array
    {
        return [
            'categories_name',
            'categories_image',
            'categories_image_url',
            'orderby',
            'status',
            'is_trending',
        ];
    }

    public function array(): array
    {
        return [
            ['Electronics', 'electronics.jpg', '', 1, 'active', 'yes'],
            ['Fashion', '', 'https://example.com/fashion.jpg', 2, 'active', 'no'],
        ];
    }
}
