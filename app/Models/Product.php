<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Product extends Model
{
    use SoftDeletes;
    protected $guarded = [];

    protected $appends = ['full_image_url'];

    /**
     * Accessor for full image url
     */
    public function getFullImageUrlAttribute()
    {
        $filePath = config('image_path.file_path') ?? url('/');

        if (!empty($this->product_image)) {
            return $filePath . 'storage/' . ltrim($this->product_image, '/');
        }

        if (!empty($this->product_image_url)) {
            return $this->product_image_url;
        }

        return null;
    }

    // public function productCategories(): HasMany
    // {
    //     return $this->hasMany(ProductCategory::class, 'product_id');
    // }   

    public function productCategories(): HasMany
    {
        return $this->hasMany(ProductCategory::class)
            ->whereNull('deleted_at');
    }

    public function productSpecialities(): HasMany
    {
        return $this->hasMany(ProductSpeciality::class)
            ->whereNull('deleted_at');
    }


    /*public function categories(): BelongsToMany
    {
        return $this->belongsToMany(Category::class, 'product_categories', 'product_id', 'category_id')
            ->withPivot(['subcategory_id', 'status'])
            ->withTimestamps();
    }

    public function subCategories(): BelongsToMany
    {
        return $this->belongsToMany(SubCategory::class, 'product_categories', 'product_id', 'subcategory_id')
            ->withPivot(['category_id', 'status'])
            ->withTimestamps();
    }

    public function badgeProducts(): HasMany
    {
        return $this->hasMany(BadgeProduct::class, 'product_id');
    }

    public function badges(): BelongsToMany
    {
        return $this->belongsToMany(Badge::class, 'badge_products', 'product_id', 'badge_id')
            ->withPivot(['status'])
            ->withTimestamps();
    }*/

    public function category()
    {
        return $this->belongsTo(Category::class, 'category_id');
    }

    public function subCategory()
    {
        return $this->belongsTo(Category::class, 'subcategory_id');
    }

    public function speciality()
    {
        return $this->belongsTo(Speciality::class, 'speciality_id');
    }

    public function subSpeciality()
    {
        return $this->belongsTo(Speciality::class, 'sub_speciality_id');
    }

    public function productVideos(): HasMany
    {
        return $this->hasMany(ProductVideo::class, 'product_id');
    }

    public function productInformations(): HasMany
    {
        return $this->hasMany(ProductInformation::class, 'product_id');
    }

    public function productCompatible(): HasMany
    {
        return $this->hasMany(RelatedProduct::class, 'product_id');
    }

    public function productTestimonial(): HasMany
    {
        return $this->hasMany(Testimonial::class, 'product_id');
    }

    public function productTextTestimonial(): HasMany
    {
        return $this->hasMany(Testimonial::class, 'product_id')->where('type', 'text');
    }

    public function productVideoTestimonial(): HasMany
    {
        return $this->hasMany(Testimonial::class, 'product_id')->where('type', 'video');
    }
}
