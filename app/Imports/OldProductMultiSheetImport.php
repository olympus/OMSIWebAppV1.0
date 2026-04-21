<?php

namespace App\Imports;

use App\Models\Product;
use App\Models\ProductVideo;
use App\Models\ProductInformation;
use App\Models\Category;
use App\Models\Speciality;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;

class OldProductMultiSheetImport implements WithMultipleSheets
{
    public function sheets(): array
    {
        return [
            'Products' => new ProductSheetImport(),
            'Product Videos' => new ProductVideoSheetImport(),
            'Product Information' => new ProductInformationSheetImport(),
        ];
    }
}

class ProductSheetImport implements \Maatwebsite\Excel\Concerns\ToModel, \Maatwebsite\Excel\Concerns\WithHeadingRow
{
    public function model(array $row)
    {
        // Find or create related models
        $category = Category::where('categories_name', $row['category'])->first();
        $subCategory = Category::where('categories_name', $row['sub_category'])->first();
        $speciality = Speciality::where('specialities_name', $row['speciality'])->first();
        $subSpeciality = Speciality::where('specialities_name', $row['sub_speciality'])->first();

        return new Product([
            'product_name' => $row['product_name'],
            'slug' => $row['slug'],
            'heading' => $row['heading'],
            'sub_heading' => $row['sub_heading'],
            'category_id' => $category ? $category->id : null,
            'sub_category_id' => $subCategory ? $subCategory->id : null,
            'speciality_id' => $speciality ? $speciality->id : null,
            'sub_speciality_id' => $subSpeciality ? $subSpeciality->id : null,
            'product_image' => $row['product_image'],
            'short_description' => $row['short_description'],
            'long_description' => $row['long_description'],
            'status' => strtolower($row['status']) === 'active' ? 1 : 0,
        ]);
    }
}

class ProductVideoSheetImport implements \Maatwebsite\Excel\Concerns\ToModel, \Maatwebsite\Excel\Concerns\WithHeadingRow
{
    public function model(array $row)
    {
        $product = Product::where('product_name', $row['product_name'])->first();

        if ($product) {
            return new ProductVideo([
                'product_id' => $product->id,
                'video_title' => $row['video_title'],
                'video_url' => $row['video_url'],
                'video_thumbnail' => $row['video_thumbnail'],
                'video_alt_text' => $row['video_alt_text'],
                'status' => strtolower($row['status']) === 'active' ? 1 : 0,
            ]);
        }

        return null;
    }
}

class ProductInformationSheetImport implements \Maatwebsite\Excel\Concerns\ToModel, \Maatwebsite\Excel\Concerns\WithHeadingRow
{
    public function model(array $row)
    {
        $product = Product::where('product_name', $row['product_name'])->first();

        if ($product) {
            return new ProductInformation([
                'product_id' => $product->id,
                'title' => $row['title'],
                'description' => $row['description'],
                'file_upload' => $row['file_upload'],
                'order' => $row['order'],
                'status' => strtolower($row['status']) === 'active' ? 1 : 0,
            ]);
        }

        return null;
    }
}