<?php

namespace App\Exports;

use App\Models\Category;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;
use Carbon\Carbon;

class CategoryMultiSheetExport implements WithMultipleSheets
{
    protected $startDate;
    protected $endDate;

    public function __construct($startDate = null, $endDate = null)
    {
        $this->startDate = $startDate ? Carbon::parse($startDate)->startOfDay() : null;
        $this->endDate = $endDate ? Carbon::parse($endDate)->endOfDay() : null;
    }

    public function sheets(): array
    {
        return [
            new CategorySheet($this->startDate, $this->endDate),
            new SubCategorySheet($this->startDate, $this->endDate),
            //new SubSubCategorySheet($this->startDate, $this->endDate),
        ];
    }
}

class CategorySheet implements FromCollection, WithHeadings, WithMapping, WithTitle
{
    protected $startDate;
    protected $endDate;

    public function __construct($startDate = null, $endDate = null)
    {
        $this->startDate = $startDate;
        $this->endDate = $endDate;
    }

    public function title(): string
    {
        return 'Categories';
    }

    public function collection()
    {
        $query = Category::whereNull('parent_id')->whereNull('child_id');

        if ($this->startDate && $this->endDate) {
            $query->whereBetween('created_at', [$this->startDate, $this->endDate]);
        }

        return $query->get();
    }

    public function headings(): array
    {
        return ['id', 'categories_name', 'slug', 'categories_image', 'categories_image_url', 'orderby', 'is_trending', 'status', 'created_at', 'updated_at'];
        //return ['ID', 'Name', 'Slug', 'Image', 'Image Url', 'Order', 'Is Trending', 'Status', 'Created At', 'Updated At'];
    }

    public function map($row): array
    {
        return [
            $row->id,
            $row->categories_name,
            $row->slug,
            $row->categories_image,
            $row->categories_image_url,
            $row->orderby,
            $row->is_trending ? 'Yes' : 'No',
            $row->status ? 'Active' : 'Inactive',
            $row->created_at,
            $row->updated_at,  
        ];
    }
}

class SubCategorySheet implements FromCollection, WithHeadings, WithMapping, WithTitle
{
    protected $startDate;
    protected $endDate;

    public function __construct($startDate = null, $endDate = null)
    {
        $this->startDate = $startDate;
        $this->endDate = $endDate;
    }

    public function title(): string
    {
        return 'Sub Categories';
    }

    public function collection()
    {
        $query = Category::whereNotNull('parent_id')->whereNull('child_id')->with('parent');

        if ($this->startDate && $this->endDate) {
            $query->whereHas('parent', function ($q) {
                $q->whereBetween('created_at', [$this->startDate, $this->endDate]);
            });
        }

        return $query->get();
    }

    public function headings(): array
    {   
        return ['id', 'parent_category', 'categories_name', 'slug', 'categories_image', 'categories_image_url', 'orderby', 'is_trending', 'status', 'created_at', 'updated_at'];
        //return ['ID', 'Category', 'Name', 'Slug', 'Image', 'Image Url', 'Order', 'Is Trending', 'Status', 'Created At', 'Updated At']; 
        //return ['ID', 'Category', 'Name', 'Slug', 'Image', 'Image Url', 'Order', 'Is Trending', 'Status', 'Created At', 'Updated At']; 
    }

    public function map($row): array
    {
        return [
            $row->id,
            $row->parent->categories_name ?? '',
            $row->categories_name,
            $row->slug,
            $row->categories_image,
            $row->categories_image_url,
            $row->orderby, 
            $row->is_trending ? 'Yes' : 'No',
            $row->status ? 'Active' : 'Inactive',
            $row->created_at,
            $row->updated_at,   
        ];
    }
}

/*class SubSubCategorySheet implements FromCollection, WithHeadings, WithMapping, WithTitle
{
    protected $startDate;
    protected $endDate;

    public function __construct($startDate = null, $endDate = null)
    {
        $this->startDate = $startDate;
        $this->endDate = $endDate;
    }

    public function title(): string
    {
        return 'Sub Sub Categories';
    }

    public function collection()
    {
        $query = Category::whereNotNull('parent_id')->whereNotNull('child_id')->with(['parent', 'child']);

        if ($this->startDate && $this->endDate) {
            $query->whereHas('parent', function ($q) {
                $q->whereBetween('created_at', [$this->startDate, $this->endDate]);
            });
        }

        return $query->get();
    }

    public function headings(): array
    {
        return ['ID', 'Category', 'Sub Category', 'Sub Sub Category Name', 'Image', 'Order', 'Status'];
    }

    public function map($row): array
    {
        return [
            $row->id,
            $row->parent->categories_name ?? '',
            $row->child->categories_name ?? '',
            $row->categories_name,
            $row->categories_image,
            $row->orderby,
            $row->status ? 'Active' : 'Inactive',
        ];
    }
}*/
