<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ProductInformation extends Model
{
    use SoftDeletes;
    protected $guarded = [];

    protected $appends = ['full_pdf_url'];

    public function product()
    {
        return $this->belongsTo(Product::class, 'product_id');
    }
    
    public function getFullPdfUrlAttribute()
    {
        $filePath = config('image_path.file_path') ?? url('/');

        if (!empty($this->file_upload)) {
            return $filePath . 'storage/' . ltrim($this->file_upload, '/');
        }

        if (!empty($this->file_url)) {
            return $this->file_url;
        }

        return null;
    }
}
