<?php

namespace App\Imports;

use App\Models\Speciality;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithChunkReading;

class SpecialityMultiSheetImport implements WithMultipleSheets
{
    protected int $userId;

    public function __construct()
    {
        $this->userId = auth()->id() ?? 1;
    }

    public function sheets(): array
    {
        return [
            'Specialities'     => new SpecialitySheetImport($this->userId),
            'Sub Specialities' => new SubSpecialitySheetImport($this->userId),
        ];
    }
}

class SpecialitySheetImport implements ToCollection, WithHeadingRow, WithChunkReading
{
    protected int $userId;

    public function __construct($userId)
    {
        $this->userId = $userId;
    }

    public function collection(Collection $rows)
    {
        if ($rows->isEmpty()) {
            return;
        }

        $now = now();

        foreach ($rows as $row) {

            $name = trim($row['specialities_name'] ?? '');

            if (!$name) {
                continue;
            }

            $normalizedName = strtolower($name);

            $existing = Speciality::whereNull('parent_id')
                ->whereRaw('LOWER(specialities_name) = ?', [$normalizedName])
                ->first();

            $data = [

                'specialities_name'      => $name,
                'slug'                   => Str::slug($name),
                'specialities_image'     => $row['specialities_image'] ?? null,
                'specialities_image_url' => $row['specialities_image_url'] ?? null,
                'orderby'                => $row['orderby'] ?? null,
                'status'                 => strtolower($row['status'] ?? 'active') === 'active' ? 1 : 0,
                'is_trending'            => strtolower($row['is_trending'] ?? 'no') === 'yes' ? 1 : 0,
                'updated_at'             => $now,

            ];

            if ($existing) {

                $existing->update($data);

            } else {

                $data['parent_id']  = null;
                $data['child_id']   = null;
                $data['created_by'] = $this->userId;
                $data['created_at'] = $now;

                Speciality::create($data);

            }
        }
    }

    public function chunkSize(): int
    {
        return 1000;
    }
}

class SubSpecialitySheetImport implements ToCollection, WithHeadingRow, WithChunkReading
{
    protected int $userId;

    public function __construct($userId)
    {
        $this->userId = $userId;
    }

    public function collection(Collection $rows)
    {
        if ($rows->isEmpty()) {
            return;
        }

        $now = now();

        // Load parents once
        $parents = Speciality::whereNull('parent_id')
            ->get()
            ->mapWithKeys(function ($item) {

                return [

                    strtolower(trim($item->specialities_name)) => $item->id

                ];

            });

        foreach ($rows as $row) {

            $name = trim($row['specialities_name'] ?? '');
            $parentName = strtolower(trim($row['parent_category'] ?? ''));

            if (!$name || !$parentName) {

                continue;

            }

            if (!isset($parents[$parentName])) {

                continue;

            }

            $parentId = $parents[$parentName];

            // Validate specialities_name length
            if (strlen($name) > 75) {
                throw new \Exception("Sub-speciality name '{$name}' exceeds maximum length of 75 characters.");
            }

            // Validate specialities_name regex
            if (!preg_match('/^[A-Za-z0-9]+( [A-Za-z0-9]+)*$/', $name)) {
                throw new \Exception("Sub-speciality name '{$name}' contains invalid characters. Only letters, numbers, and single spaces are allowed.");
            }

            // Check uniqueness within parent (case-insensitive, ignoring deleted)
            $normalizedName = strtolower($name);
            $existingUnique = Speciality::whereNull('deleted_at')
                ->where('parent_id', $parentId)
                ->whereRaw('LOWER(specialities_name) = ?', [$normalizedName])
                ->first();

            if ($existingUnique) {
                throw new \Exception("Sub-speciality name '{$name}' already exists under parent '{$parentName}'.");
            }

            // Validate slug uniqueness
            $slug = Str::slug($name);
            if (strlen($slug) > 75) {
                throw new \Exception("Generated slug for '{$name}' exceeds maximum length of 75 characters.");
            }

            if (!preg_match('/^[a-z0-9]+(-[a-z0-9]+)*$/', $slug)) {
                throw new \Exception("Generated slug for '{$name}' contains invalid characters.");
            }

            $existingSlug = Speciality::whereNull('deleted_at')
                ->where('slug', $slug)
                ->first();

            if ($existingSlug) {
                throw new \Exception("Slug '{$slug}' already exists.");
            }

            // Validate image requirements
            $image = $row['specialities_image'] ?? null;
            $imageUrl = $row['specialities_image_url'] ?? null;

            if (!$image && !$imageUrl) {
                throw new \Exception("Either specialities_image or specialities_image_url is required for '{$name}'.");
            }

            if ($image && $imageUrl) {
                throw new \Exception("Only one of specialities_image or specialities_image_url is allowed for '{$name}'.");
            }

            // Validate orderby if provided
            $orderby = $row['orderby'] ?? null;
            if ($orderby !== null && (!is_numeric($orderby) || $orderby < 1)) {
                throw new \Exception("Orderby must be a number greater than 0 for '{$name}'.");
            }

            $existing = Speciality::where('parent_id', $parentId)
                ->whereRaw('LOWER(specialities_name) = ?', [strtolower($name)])
                ->first();

            $data = [

                'specialities_name'      => $name,
                'slug'                   => Str::slug($name),
                'specialities_image'     => $image,
                'specialities_image_url' => $imageUrl,
                'orderby'                => $orderby,
                'status'                 => strtolower($row['status'] ?? 'active') === 'active' ? 1 : 0,
                'is_trending'            => strtolower($row['is_trending'] ?? 'no') === 'yes' ? 1 : 0,
                'parent_id'              => $parentId,
                'updated_at'             => $now,

            ];

            if ($existing) {

                $existing->update($data);

            } else {

                $data['child_id']   = null;
                $data['created_by'] = $this->userId;
                $data['created_at'] = $now;

                Speciality::create($data);

            }
        }
    }

    public function chunkSize(): int
    {
        return 1000;
    }
}
