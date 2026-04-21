<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use DB;

class Category extends Model
{
    use SoftDeletes;
    protected $table = 'categories';

    protected $guarded = [];

    protected $appends = ['full_image_url'];

    protected static function booted()
    {
        static::deleting(function ($category) {

            // delete sub categories (soft delete because model use kar raha hai)
            $subCategories = self::where('parent_id', $category->id)->get();

            foreach ($subCategories as $subCategory) {
                $subCategory->delete();
            }

            // soft delete related product categories
            DB::table('product_categories')
                ->where(function ($query) use ($category) {
                    $query->where('category_id', $category->id)
                          ->orWhere('subcategory_id', $category->id);
                })
                ->update([
                    'deleted_at' => now(),
                    'updated_at' => now(),
                ]);
        });
    }
    // protected static function boot()
    // {
    //     parent::boot();

    //     // When category is deleted
    //     static::deleting(function ($category) {

    //         // If soft delete
    //         if ($category->isForceDeleting()) {
    //             // Permanently delete children
    //             $category->subCategories()->withTrashed()->get()->each->forceDelete();
    //         } else {
    //             // Soft delete children
    //             $category->subCategories()->get()->each->delete();
    //         }
    //     });

    //     // When category is restored
    //     static::restoring(function ($category) {
    //         $category->subCategories()->withTrashed()->get()->each->restore();
    //     });
    // }

    /**
     * Accessor for full image url
     */
    public function getFullImageUrlAttribute()
    {
        $filePath = config('image_path.file_path') ?? url('/');

        if (!empty($this->categories_image)) {
            return $filePath . 'storage/' . ltrim($this->categories_image, '/');
        }

        if (!empty($this->categories_image_url)) {
            return $this->categories_image_url;
        }

        return null;
    }
    
    // public function subCategories()
    // {
    //     return $this->hasMany(SubCategory::class, 'category_id', 'id');
    // }

    // Children categories stored in the same table via parent_id
    public function subCategories()
    {
        return $this->hasMany(self::class, 'parent_id', 'id')->whereNull('child_id');
    }

    public function parent()
    {
        return $this->belongsTo(self::class, 'parent_id', 'id');
    }

    /**
     * If this Category record links to another Category as its inner child, return that Category.
     */
    public function child()
    {
        return $this->belongsTo(self::class, 'child_id', 'id');
    }

    /**
     * Sub-subcategories: Category rows that are linked to a SubCategory via `child_id`.
     * parent_id = this category id, child_id = subcategory id
     */
    public function subSubCategories()
    {
        return $this->hasMany(self::class, 'parent_id', 'id')
            ->whereNotNull('child_id');
    }

    // public function products(): \Illuminate\Database\Eloquent\Relations\BelongsToMany
    // {
    //     return $this->belongsToMany(Product::class, 'product_categories', 'category_id', 'product_id')
    //         ->withPivot(['subcategory_id', 'status'])
    //         ->withTimestamps();
    // }

    public function products()
    {
        return $this->hasMany(Product::class, 'category_id');
    }

    public function subCategoriesData()
    {
        return $this->hasMany(Category::class, 'parent_id')
            ->where('status', 1)
            ->orderByRaw('orderby IS NULL')
            ->orderBy('orderby', 'asc')
            ->select([
                'id as category_id',
                'parent_id',
                'categories_name',
                'categories_image',
                'categories_image_url'
            ]);
    }


}
