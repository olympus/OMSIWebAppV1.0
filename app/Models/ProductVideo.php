<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ProductVideo extends Model
{
    use SoftDeletes;
    protected $guarded = [];

    protected $appends = ['full_video_thumbnail_url', 'full_video_url'];

    public function product()
    {
        return $this->belongsTo(Product::class, 'product_id');
    }

    public function getFullVideoThumbnailUrlAttribute()
    {
        $filePath = config('image_path.file_path') ?? url('/');

        if (!empty($this->video_thumbnail)) {
            return $filePath . 'storage/' . ltrim($this->video_thumbnail, '/');
        }

        // if (!empty($this->categories_image_url)) {
        //     return $this->categories_image_url;
        // }

        return null;
    }

    public function getFullVideoUrlAttribute()
    {
        $filePath = config('image_path.file_path') ?? url('/');

        if (!empty($this->video_file)) {
            return $filePath . 'storage/' . ltrim($this->video_file, '/');
        }

        if (!empty($this->video_url)) {
            return $this->video_url;
        }

        return null;
    }
}
