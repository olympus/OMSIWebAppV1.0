<?php
namespace App\Exports;

use App\Models\Product;
use App\Models\ProductVideo;
use App\Models\ProductInformation;
use App\Models\RelatedProduct;
use App\Models\ProductCategory;
use App\Models\ProductSpeciality;

use Maatwebsite\Excel\Concerns\WithMultipleSheets;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithTitle;

class ProductMultiSheetExport implements WithMultipleSheets
{
    public function sheets(): array
    {
        return [
            new ProductSheetExport(),
            new ProductVideoSheetExport(),
            new ProductInformationSheetExport(),
            new RelatedProductSheetExport(),
            new ProductCategorySheetExport(),
            new ProductSpecialitySheetExport(),
        ];
    }
}

class ProductSheetExport implements FromCollection, WithHeadings, WithMapping, WithTitle
{
    public function title(): string
    {
        return 'Products';
    }

    public function collection()
    {
        return Product::get();
    }

    public function headings(): array
    {
        return [
            'product_sku',
            'product_name',
            'slug',
            'heading',
            'sub_heading',
            'short_description',
            'long_description',
            'product_image',
            'product_image_url',
            'status',
            'is_trending',
            'is_new',
        ];
    }

    public function map($row): array
    {
        return [
            $row->product_sku,
            $row->product_name,
            $row->slug,
            $row->heading,
            $row->sub_heading,
            strip_tags($row->short_description),
            strip_tags($row->long_description),
            $row->product_image,
            $row->product_image_url,
            $row->status ? 'active' : 'inactive',
            $row->is_trending ? 'yes' : 'no',
            $row->is_new ? 'yes' : 'no',
        ];
    }
}

class ProductVideoSheetExport implements FromCollection, WithHeadings, WithMapping, WithTitle
{
    public function title(): string
    {
        return 'Product Videos';
    }

    public function collection()
    {
        return ProductVideo::with('product')->get();
    }

    public function headings(): array
    {
        return [
            'product_sku',
            'video_title',
            'video_url',
            'video_file',
            'video_thumbnail',
            'video_alt_text',
            'status',
        ];
    }

    public function map($row): array
    {
        return [
            $row->product->product_sku ?? '',
            $row->video_title,
            $row->video_url,
            $row->video_file,
            $row->video_thumbnail,
            $row->video_alt_text,
            $row->status ? 'active' : 'inactive',
        ];
    }
}

class ProductInformationSheetExport implements FromCollection, WithHeadings, WithMapping, WithTitle
{
    public function title(): string
    {
        return 'Product Information';
    }

    public function collection()
    {
        return ProductInformation::with('product')->get();
    }

    public function headings(): array
    {
        return [
            'product_sku',
            'title',
            'description',
            'file_upload',
            'file_url',
            'order',
            'status',
        ];
    }

    public function map($row): array
    {
        return [
            $row->product->product_sku ?? '',
            $row->title,
            $row->description,
            $row->file_upload,
            $row->file_url,
            $row->order,
            $row->status ? 'active' : 'inactive',
        ];
    }
}

class RelatedProductSheetExport implements FromCollection, WithHeadings, WithMapping, WithTitle
{
    public function title(): string
    {
        return 'Related Products';
    }

    public function collection()
    {
        return RelatedProduct::with(['product','compatibleProduct'])->get();
    }

    public function headings(): array
    {
        return [
            'product_sku',
            'related_product_sku',
            'status',
        ];
    }

    public function map($row): array
    {
        return [
            $row->product->product_sku ?? '',
            $row->relatedProduct->product_sku ?? '',
            $row->status ? 'active' : 'inactive',
        ];
    }
}

class ProductCategorySheetExport implements FromCollection, WithHeadings, WithMapping, WithTitle
{
    public function title(): string
    {
        return 'Product Categories';
    }

    public function collection()
    {
        return ProductCategory::with(['product','category','subcategory'])->get();
    }

    public function headings(): array
    {
        return [
            'product_sku',
            'category_name',
            'sub_category_name',
        ];
    }

    public function map($row): array
    {
        return [
            $row->product->product_sku ?? '',
            $row->category->categories_name ?? '',
            $row->subcategory->categories_name ?? '',
        ];
    }
}

class ProductSpecialitySheetExport implements FromCollection, WithHeadings, WithMapping, WithTitle
{
    public function title(): string
    {
        return 'Product Specialities';
    }

    public function collection()
    {
        return ProductSpeciality::with(['product','speciality','subSpeciality'])->get();
    }

    public function headings(): array
    {
        return [
            'product_sku',
            'speciality_name',
            'sub_speciality_name',
        ];
    }

    public function map($row): array
    {
        return [
            $row->product->product_sku ?? '',
            $row->speciality->specialities_name ?? '',
            $row->subSpeciality->specialities_name ?? '',
        ];
    }
}