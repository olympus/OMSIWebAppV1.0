<?php

namespace App\Imports;

use App\Models\Product;
use App\Models\ProductVideo;
use App\Models\ProductInformation;
use App\Models\RelatedProduct;
use App\Models\Category;
use App\Models\Speciality;

use Maatwebsite\Excel\Concerns\WithMultipleSheets;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithValidation;
use Maatwebsite\Excel\Concerns\WithBatchInserts;
use Illuminate\Validation\Rule;
use Illuminate\Support\Str;

class ProductMultiSheetImport implements WithMultipleSheets
{
    public function sheets(): array
    {
        return [
            'Products' => new ProductSheetImport(),
            'Product Videos' => new ProductVideoSheetImport(),
            'Product Information' => new ProductInformationSheetImport(),
            'Related Products' => new RelatedProductSheetImport(),
            'Product Categories' => new ProductCategorySheetImport(), // 👈 NEW
            'Product Specialities' => new ProductSpecialitySheetImport(), // 👈 NEW
        ];
    }
}


class ProductSheetImport implements ToModel, WithHeadingRow, WithValidation
{
    public function model(array $row)
    {
        $product = Product::updateOrCreate(
            ['product_sku' => $row['product_sku']],
            [
                'product_name' => $row['product_name'],
                'slug' => $row['slug'] ?? Str::slug($row['product_name']),
                'heading' => $row['heading'] ?? null,
                'sub_heading' => $row['sub_heading'] ?? null,
                'short_description' => $row['short_description'] ?? null,
                'long_description' => $row['long_description'] ?? null,
                'product_image' => $row['product_image'] ?? null,
                'product_image_url' => $row['product_image_url'] ?? null,
                'status' => strtolower($row['status']) === 'active' ? 1 : 0,
                'is_trending' => strtolower($row['is_trending']) === 'yes' ? 1 : 0,
                'is_new' => strtolower($row['is_new']) === 'yes' ? 1 : 0,
            ]
        );
 
        return $product;
    }

    public function rules(): array
    {
        return [
            'product_name' => [
                'required',
                'min:2',
                'max:100',
                'regex:/^[a-zA-Z0-9\-]+( [a-zA-Z0-9\-]+)*$/',
            ],
            'product_sku' => [
                'required',
                'max:30',
            ],
            'status' => [
                'required',
                'in:active,inactive,Active,Inactive',
            ],
            'is_trending' => [
                'required',
                'in:yes,no,Yes,No',
            ],
            'is_new' => [
                'required',
                'in:yes,no,Yes,No',
            ],
            'short_description' => [
                'nullable',
                'max:300',
            ],
            'long_description' => [
                'nullable',
                'max:1200',
            ],
            'product_image_url' => [
                'nullable',
                'url',
            ],
        ];
    }

    public function customValidationMessages()
    {
        return [
            'product_name.regex' => 'Product name must start/end with alphanumeric characters/hyphens and contain only single spaces between words.',
            'status.in' => 'Status must be active or inactive.',
            'is_trending.in' => 'Is Trending must be yes or no.',
            'is_new.in' => 'Is New must be yes or no.',
        ];
    }
}


class ProductVideoSheetImport implements ToModel, WithHeadingRow, WithValidation
{
    public function model(array $row)
    {
        $product = Product::where('product_sku', $row['product_sku'])->first();
        if (!$product) return null;

        return ProductVideo::updateOrCreate(
            [
                'product_id' => $product->id,
                'video_title' => $row['video_title'],
            ],
            [
                'video_url' => $row['video_url'],
                'video_file' => $row['video_file'],
                'video_thumbnail' => $row['video_thumbnail'],
                'video_alt_text' => $row['video_alt_text'],
                'status' => strtolower($row['status']) === 'active' ? 1 : 0,
            ]
        );
    }

    public function rules(): array
    {
        return [
            'product_sku' => 'required',
            'video_title' => 'required|max:100',
            'video_url' => 'nullable|url',
            'status' => 'required|in:active,inactive,Active,Inactive',
        ];
    }
}


class ProductInformationSheetImport implements ToModel, WithHeadingRow, WithValidation
{
    public function model(array $row)
    {
        $product = Product::where('product_sku', $row['product_sku'])->first();
        if (!$product) return null;

        return ProductInformation::updateOrCreate(
            [
                'product_id' => $product->id,
                'title' => $row['title'],
            ],
            [
                'description' => $row['description'],
                'file_upload' => $row['file_upload'],
                'file_url' => $row['file_url'],
                'order' => $row['order'],
                'status' => strtolower($row['status']) === 'active' ? 1 : 0,
            ]
        );
    }

    public function rules(): array
    {
        return [
            'product_sku' => 'required',
            'title' => [
                'required',
                'max:100',
                'regex:/^[a-zA-Z0-9\-]+( [a-zA-Z0-9\-]+)*$/',
            ],
            'order' => 'required|numeric|min:0',
            'status' => 'required|in:active,inactive,Active,Inactive',
        ];
    }

    public function customValidationMessages()
    {
        return [
            'title.regex' => 'Title must start/end with alphanumeric characters/hyphens and contain only single spaces between words.',
        ];
    }
}

class RelatedProductSheetImport implements ToModel, WithHeadingRow
{
    public function model(array $row)
    {
        $product = Product::where('product_sku', $row['product_sku'])->first();
        $related = Product::where('product_sku', $row['related_product_sku'])->first();

        if (!$product || !$related) return null;

        return RelatedProduct::updateOrCreate(
            [
                'product_id' => $product->id,
                'compatible_product_id' => $related->id,
            ],
            [
                'status' => strtolower($row['status']) === 'active' ? 1 : 0,
            ]
        );
    }
}

class ProductCategorySheetImport implements ToModel, WithHeadingRow, WithValidation
{
    public function model(array $row)
    {
        $product = Product::where('product_sku', $row['product_sku'])->first();
        if (!$product) return null;

        $category = Category::where('categories_name', $row['category_name'])
            ->whereNull('parent_id')
            ->first();
        if (!$category) return null;

        $subCategory = Category::where('categories_name', $row['sub_category_name'])
            ->where('parent_id', $category->id)
            ->first();
        if (!$subCategory) return null;

        $product->productCategories()->updateOrCreate(
            [
                'product_id' => $product->id,
                'category_id' => $category->id,
            ],
            [
                'subcategory_id' => $subCategory->id,
                'status' => 1,
            ]
        );

        return null; // Pivot entry only
    }

    public function rules(): array
    {
        return [
            'product_sku' => 'required|exists:products,product_sku',
            'category_name' => 'required|exists:categories,categories_name',
            'sub_category_name' => 'required|exists:categories,categories_name',
        ];
    }
}

class ProductSpecialitySheetImport implements ToModel, WithHeadingRow
{
    public function model(array $row)
    {
        $product = Product::where('product_sku', $row['product_sku'])->first();
        if (!$product) return null;

        $speciality = Speciality::where('specialities_name', $row['speciality_name'])->first();
        if (!$speciality) return null;

        $subSpeciality = null;
        if (!empty($row['sub_speciality_name'])) {
            $subSpeciality = Speciality::where('specialities_name', $row['sub_speciality_name'])->first();
        }

        $product->productSpecialities()->updateOrCreate(
            [
                'product_id' => $product->id,
                'speciality_id' => $speciality->id,
            ],
            [
                'sub_speciality_id' => $subSpeciality?->id,
                'status' => 1,
            ]
        );

        return null; // Only pivot entry
    }
}