<?php

namespace App\Imports;

use App\Models\Category;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithChunkReading;

class CategoryMultiSheetImport implements WithMultipleSheets
{
    protected int $userId;

    public CategorySheetImport $categorySheet;
    public SubCategorySheetImport $subCategorySheet;

    public function __construct()
    {
        $this->userId = auth()->id() ?? 1;

        // SAME instance (important)
        $this->categorySheet = new CategorySheetImport($this->userId);
        $this->subCategorySheet = new SubCategorySheetImport($this->userId);
    }

    public function sheets(): array
    {
        return [
            'Categories'     => $this->categorySheet,
            'Sub Categories' => $this->subCategorySheet,
        ];
    }

    public function getAllErrors(): array
    {
        return array_merge(
            $this->categorySheet->getErrors(),
            $this->subCategorySheet->getErrors()
        );
    }
}

class CategorySheetImport implements ToCollection, WithHeadingRow, WithChunkReading
{
    protected int $userId;
    protected array $errors = [];

    public function __construct($userId)
    {
        $this->userId = $userId;
    }

    public function collection(Collection $rows)
    {
        if ($rows->isEmpty()) return;

        $now = now();

        foreach ($rows as $index => $row) {

            $rowNumber = $index + 2; // heading row skip

            $name = trim($row['categories_name'] ?? '');

            if (!$name) continue;

            if (strlen($name) > 75) {
                $this->errors[] = "Row {$rowNumber}: '{$name}' max length exceeded.";
                continue;
            }

            if (!preg_match('/^[A-Za-z0-9]+( [A-Za-z0-9]+)*$/', $name)) {
                $this->errors[] = "Row {$rowNumber}: Invalid name '{$name}'.";
                continue;
            }

            $normalizedName = strtolower($name);

            $slug = \Str::slug($name);

            $image = $row['categories_image'] ?? null;
            $imageUrl = $row['categories_image_url'] ?? null;

            if (!$image && !$imageUrl) {
                $this->errors[] = "Row {$rowNumber}: Image or URL required for '{$name}'.";
                continue;
            }

            if ($image && $imageUrl) {
                $this->errors[] = "Row {$rowNumber}: Only one image allowed for '{$name}'.";
                continue;
            }

            $existing = \App\Models\Category::whereNull('parent_id')
                ->whereRaw('LOWER(categories_name) = ?', [$normalizedName])
                ->first();

            $data = [
                'categories_name'      => $name,
                'slug'                 => $slug,
                'categories_image'     => $image,
                'categories_image_url' => $imageUrl,
                'orderby'              => $row['orderby'] ?? null,
                'status'               => strtolower($row['status'] ?? 'active') === 'active' ? 1 : 0,
                'is_trending'          => strtolower($row['is_trending'] ?? 'no') === 'yes' ? 1 : 0,
                'updated_at'           => $now,
            ];

            if ($existing) {
                $existing->update($data);
            } else {
                $data['parent_id']  = null;
                $data['child_id']   = null;
                $data['created_by'] = $this->userId;
                $data['created_at'] = $now;

                \App\Models\Category::create($data);
            }
        }
    }

    public function chunkSize(): int
    {
        return 1000;
    }

    public function getErrors(): array
    {
        return $this->errors;
    }
}

class SubCategorySheetImport implements ToCollection, WithHeadingRow, WithChunkReading
{
    protected int $userId;
    protected array $errors = [];

    public function __construct($userId)
    {
        $this->userId = $userId;
    }

    public function collection(Collection $rows)
    {
        if ($rows->isEmpty()) return;

        $now = now();

        $parents = \App\Models\Category::whereNull('parent_id')
            ->get()
            ->mapWithKeys(fn ($item) => [strtolower(trim($item->categories_name)) => $item->id]);

        foreach ($rows as $index => $row) {

            $rowNumber = $index + 2;

            $name = trim($row['categories_name'] ?? '');
            $parentName = strtolower(trim($row['parent_category'] ?? ''));

            if (!$name || !$parentName) continue;

            if (!isset($parents[$parentName])) {
                $this->errors[] = "Row {$rowNumber}: Parent '{$parentName}' not found.";
                continue;
            }

            $parentId = $parents[$parentName];

            $existing = \App\Models\Category::where('parent_id', $parentId)
                ->whereRaw('LOWER(categories_name) = ?', [strtolower($name)])
                ->first();

            $data = [
                'categories_name' => $name,
                'slug' => \Str::slug($name),
                'categories_image' => $row['categories_image'] ?? null,
                'categories_image_url' => $row['categories_image_url'] ?? null,
                'orderby' => $row['orderby'] ?? null,
                'status' => strtolower($row['status'] ?? 'active') === 'active' ? 1 : 0,
                'is_trending' => strtolower($row['is_trending'] ?? 'no') === 'yes' ? 1 : 0,
                'parent_id' => $parentId,
                'updated_at' => $now,
            ];

            if ($existing) {
                $existing->update($data);
            } else {
                $data['child_id'] = null;
                $data['created_by'] = $this->userId;
                $data['created_at'] = $now;

                \App\Models\Category::create($data);
            }
        }
    }

    public function chunkSize(): int
    {
        return 1000;
    }

    public function getErrors(): array
    {
        return $this->errors;
    }
}