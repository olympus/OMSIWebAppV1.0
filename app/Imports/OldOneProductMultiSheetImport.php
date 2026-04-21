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
class OldOneProductMultiSheetImport implements WithMultipleSheets
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


class ProductSheetImport implements ToModel, WithHeadingRow
{
    public function model(array $row)
    {
        $product = Product::updateOrCreate(
            ['product_sku' => $row['product_sku']],
            [
                'product_name' => $row['product_name'],
                'slug' => $row['slug'],
                'heading' => $row['heading'] ?? null,
                'sub_heading' => $row['sub_heading'] ?? null,
                'short_description' => $row['short_description'] ?? null,
                'long_description' => $row['long_description'] ?? null,
                'product_image' => $row['product_image'] ?? null,
                'product_image_url' => $row['product_image_url'] ?? null,
                'status' => strtolower($row['status']) === 'active' ? 1 : 0,
            ]
        );
 
        return $product;
    }
}


class ProductVideoSheetImport implements ToModel, WithHeadingRow
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
}


class ProductInformationSheetImport implements ToModel, WithHeadingRow
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
                'status' => strtolower($row['status']) === 'active' ? 1 : 0,
            ]
        );
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

class ProductCategorySheetImport implements ToModel, WithHeadingRow
{
    public function model(array $row)
    {
        $product = Product::where('product_sku', $row['product_sku'])->first();
        if (!$product) return null;

        $category = Category::where('categories_name', $row['category_name'])->first();
        if (!$category) return null;

        $subCategory = null;
        if (!empty($row['sub_category_name'])) {
            $subCategory = Category::where('categories_name', $row['sub_category_name'])->first();
        }

        $product->productCategories()->updateOrCreate(
            [
                'product_id' => $product->id,
                'category_id' => $category->id,
            ],
            [
                'subcategory_id' => $subCategory?->id,
                'status' => 1,
            ]
        );

        return null; // Pivot entry only
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