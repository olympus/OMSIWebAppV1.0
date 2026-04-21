<?php

namespace App\Exports;

use App\Models\Speciality;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class SpecialityExport implements FromCollection, WithHeadings, WithMapping
{
    /**
    * @return \Illuminate\Support\Collection
    */
    public function collection()
    {
        // Only export parent specialities
        return Speciality::whereNull('parent_id')
            ->whereNull('child_id')
            ->with(['subSpecialities', 'subSubSpecialities', 'specialityCategories.category', 'specialityCategories.subcategory'])
            ->get();
    }

    public function headings(): array
    {
        return [
            'ID',
            'Name',
            'Sub Specialities',
            'Sub Sub Specialities',
            'Categories',
        ];
    }

    public function map($speciality): array
    {
        $categories = $speciality->specialityCategories->map(function ($specialityCategory) {
            $categoryName = $specialityCategory->category->name ?? '';
            $subcategoryName = $specialityCategory->subcategory->name ?? '';
            return $subcategoryName ? "$categoryName > $subcategoryName" : $categoryName;
        })->filter()->implode(', ');

        return [
            $speciality->id,
            $speciality->specialities_name,
            $speciality->subSpecialities->pluck('specialities_name')->implode(', '),
            $speciality->subSubSpecialities->pluck('specialities_name')->implode(', '),
            $categories,
        ];
    }
}
