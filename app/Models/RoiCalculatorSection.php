<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class RoiCalculatorSection extends Model
{   
    protected $appends = ['full_thumbnail_image_url', 'full_video_url'];

    protected $table = 'roi_calculator_sections';

    protected $guarded = [];

      /**
     * Accessor for full thumbnail image url
     */
    public function getFullThumbnailImageUrlAttribute()
    {
        $filePath = config('image_path.file_path') ?? url('/');

        if (!empty($this->thumbnail)) {
            return $filePath . 'storage/' . ltrim($this->thumbnail, '/');
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

        if (!empty($this->url)) {
            return $this->url;
        }

        return null;
    }
}
