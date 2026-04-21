<?php

namespace App\Imports;

use App\Models\Product;
use App\Models\Category;
use App\Models\Speciality;

trait ProductImportCache
{
    protected static $products = [];
    protected static $categories = [];
    protected static $specialities = [];

    protected function loadCaches()
    {
        if (empty(self::$products)) {
            self::$products = Product::pluck('id', 'product_sku')->toArray();
        }

        if (empty(self::$categories)) {
            self::$categories = Category::pluck('id', 'categories_name')->toArray();
        }

        if (empty(self::$specialities)) {
            self::$specialities = Speciality::pluck('id', 'specialities_name')->toArray();
        }
    }

    protected function productId($sku)
    {
        return self::$products[$sku] ?? null;
    }

    protected function categoryId($name)
    {
        return self::$categories[$name] ?? null;
    }

    protected function specialityId($name)
    {
        return self::$specialities[$name] ?? null;
    }
}
