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
        $this->startDate = $startDate;
        $this->endDate = $endDate;
    }

    public function sheets(): array
    {
        return [
            new SpecialitySheet($this->startDate, $this->endDate),
            new SubSpecialitySheet($this->startDate, $this->endDate),
            new SubSubSpecialitySheet($this->startDate, $this->endDate),
            new SpecialityCategorySheet($this->startDate, $this->endDate),
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
        return 'speciality';
    }

    public function collection()
    {
        $query = Speciality::whereNull('parent_id');
        if ($this->startDate && $this->endDate) {
            $query->whereBetween('created_at', [$this->startDate, $this->endDate]);
        }
        return $query->get();
    }

    public function headings(): array
    {
        return ['ID', 'Name', 'Image', 'Order', 'Status'];
    }

    public function map($row): array
    {
        return [
            $row->id,
            $row->specialities_name,
            $row->specialities_image,
            $row->orderby,
            $row->status ? 'Active' : 'Inactive',
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
        return 'subspeciality';
    }

    public function collection()
    {
        $query = Speciality::whereNotNull('parent_id')->whereNull('child_id')->with('parent');
        if ($this->startDate && $this->endDate) {
            $query->whereBetween('created_at', [$this->startDate, $this->endDate]);
        }
        return $query->get();
    }

    public function headings(): array
    {
        return ['ID', 'Speciality', 'Sub Speciality Name', 'Image', 'Order', 'Status'];
    }

    public function map($row): array
    {
        return [
            $row->id,
            $row->parent->specialities_name ?? '',
            $row->specialities_name,
            $row->specialities_image,
            $row->orderby,
            $row->status ? 'Active' : 'Inactive',
        ];
    }
}

class SubSubSpecialitySheet implements FromCollection, WithHeadings, WithMapping, WithTitle
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
}
