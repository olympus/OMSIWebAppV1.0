<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Testimonial extends Model
{
    use SoftDeletes;

    protected $table = 'testimonials';

    protected $guarded = [];

    protected $appends = ['full_thumbnail_image_url', 'full_video_url'];

    /**
     * Accessor for full thumbnail image url
     */
    public function getFullThumbnailImageUrlAttribute()
    {
        $filePath = config('image_path.file_path') ?? url('/');

        if (!empty($this->thumbnail_image)) {
            return $filePath . 'storage/' . ltrim($this->thumbnail_image, '/');
        }

        return null;
    }

    /**
     * Accessor for full video url
     */
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

    public function product()
    {
        return $this->belongsTo(Product::class, 'product_id')->where('status', 1);
    }
}
