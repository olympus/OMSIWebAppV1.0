<?php

namespace App\Imports;

use App\Models\Speciality;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;

class SpecialityMultiSheetImport implements WithMultipleSheets
{
    public function sheets(): array
    {
        return [
            'Specialities' => new SpecialitySheetImport(),
            'Sub Specialities' => new SubSpecialitySheetImport(),
            'Sub Sub Specialities' => new SubSubSpecialitySheetImport(),
        ];
    }
}

class SpecialitySheetImport implements \Maatwebsite\Excel\Concerns\ToModel, \Maatwebsite\Excel\Concerns\WithHeadingRow
{
    public function model(array $row)
    {
        return new Speciality([
            'specialities_name' => $row['name'],
            'specialities_image' => $row['image'],
            'orderby' => $row['order'],
            'status' => strtolower($row['status']) === 'active' ? 1 : 0,
            'parent_id' => null,
            'child_id' => null,
        ]);
    }
}

class SubSpecialitySheetImport implements \Maatwebsite\Excel\Concerns\ToModel, \Maatwebsite\Excel\Concerns\WithHeadingRow
{
    public function model(array $row)
    {
        $parent = Speciality::where('specialities_name', $row['speciality'])->whereNull('parent_id')->first();

        if ($parent) {
            return new Speciality([
                'specialities_name' => $row['sub_speciality_name'],
                'specialities_image' => $row['image'],
                'orderby' => $row['order'],
                'status' => strtolower($row['status']) === 'active' ? 1 : 0,
                'parent_id' => $parent->id,
                'child_id' => null,
            ]);
        }

        return null;
    }
}

class SubSubSpecialitySheetImport implements \Maatwebsite\Excel\Concerns\ToModel, \Maatwebsite\Excel\Concerns\WithHeadingRow
{
    public function model(array $row)
    {
        $parent = Speciality::where('specialities_name', $row['speciality'])->whereNull('parent_id')->first();
        $child = Speciality::where('specialities_name', $row['sub_speciality'])->where('parent_id', $parent->id ?? null)->first();

        if ($parent && $child) {
            return new Speciality([
                'specialities_name' => $row['sub_sub_speciality_name'],
                'specialities_image' => $row['image'],
                'orderby' => $row['order'],
                'status' => strtolower($row['status']) === 'active' ? 1 : 0,
                'parent_id' => $parent->id,
                'child_id' => $child->id,
            ]);
        }

        return null;
    }
}