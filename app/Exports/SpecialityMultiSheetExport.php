<?php

namespace App\Exports;

use App\Models\Speciality;
use App\Models\SpecialityCategory;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;
use Carbon\Carbon;

class SpecialityMultiSheetExport implements WithMultipleSheets
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
            new SpecialitySheet($this->startDate, $this->endDate),
            new SubSpecialitySheet($this->startDate, $this->endDate),
            // new SubSubSpecialitySheet($this->startDate, $this->endDate),
            // new SpecialityCategorySheet($this->startDate, $this->endDate),
        ];
    }
}

class SpecialitySheet implements FromCollection, WithHeadings, WithMapping, WithTitle
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
        return 'Speciality';
    }

    public function collection()
    {
        $query = Speciality::whereNull('parent_id')->whereNull('child_id');

        if ($this->startDate && $this->endDate) {
            $query->whereBetween('created_at', [$this->startDate, $this->endDate]);
        }

        return $query->get();
    }

    public function headings(): array
    {
        return ['id', 'specialities_name', 'slug', 'specialities_image', 'specialities_image_url', 'orderby', 'is_trending', 'status', 'created_at', 'updated_at'];
        //return ['ID', 'Name', 'Slug', 'Image', 'Image Url', 'Order', 'Is Trending', 'Status', 'Created At', 'Updated At'];
    }

    public function map($row): array
    {
        return [
            $row->id,
            $row->specialities_name,
            $row->slug,
            $row->specialities_image,
            $row->specialities_image_url,
            $row->orderby,
            $row->is_trending ? 'Yes' : 'No',
            $row->status ? 'Active' : 'Inactive',
            $row->created_at,
            $row->updated_at,  
        ];
    }
}

class SubSpecialitySheet implements FromCollection, WithHeadings, WithMapping, WithTitle
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
        $query = Speciality::whereNotNull('parent_id')->whereNull('child_id')->with('parent');

        if ($this->startDate && $this->endDate) {
            $query->whereHas('parent', function ($q) {
                $q->whereBetween('created_at', [$this->startDate, $this->endDate]);
            });
        }

        return $query->get();
    }

    public function headings(): array
    {   
        return ['id', 'parent_category', 'specialities_name', 'slug', 'specialities_image', 'specialities_image_url', 'orderby', 'is_trending', 'status', 'created_at', 'updated_at']; 
    }

    public function map($row): array
    {
        return [
            $row->id,
            $row->parent->specialities_name ?? '',
            $row->specialities_name,
            $row->slug,
            $row->specialities_image,
            $row->specialities_image_url,
            $row->orderby,
            $row->is_trending ? 'Yes' : 'No',
            $row->status ? 'Active' : 'Inactive',
            $row->created_at,
            $row->updated_at,   
        ];
    }
}

/*class SubSubSpecialitySheet implements FromCollection, WithHeadings, WithMapping, WithTitle
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
        return 'subsubspeciality';
    }

    public function collection()
    {
        $query = Speciality::whereNotNull('parent_id')->whereNotNull('child_id')->with(['parent', 'child']);
        if ($this->startDate && $this->endDate) {
            $query->whereBetween('created_at', [$this->startDate, $this->endDate]);
        }
        return $query->get();
    }

    public function headings(): array
    {
        return ['ID', 'Speciality', 'Sub Speciality', 'Sub Sub Speciality Name', 'Image', 'Order', 'Status'];
    }

    public function map($row): array
    {
        return [
            $row->id,
            $row->parent->specialities_name ?? '',
            $row->child->specialities_name ?? '',
            $row->specialities_name,
            $row->specialities_image,
            $row->orderby,
            $row->status ? 'Active' : 'Inactive',
        ];
    }
}

class SpecialityCategorySheet implements FromCollection, WithHeadings, WithMapping, WithTitle
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
        return 'specialitycategory';
    }

    public function collection()
    {
        $query = SpecialityCategory::with(['speciality', 'category', 'subcategory']);
        if ($this->startDate && $this->endDate) {
            $query->whereBetween('created_at', [$this->startDate, $this->endDate]);
        }
        return $query->get();
    }

    public function headings(): array
    {
        return ['ID', 'Speciality', 'Category', 'Sub Category', 'Order', 'Status'];
    }

    public function map($row): array
    {
        return [
            $row->id,
            $row->speciality->specialities_name ?? '',
            $row->category->categories_name ?? '',
            $row->subcategory->categories_name ?? '',
            $row->order,
            $row->status ? 'Active' : 'Inactive',
        ];
    }
}*/
