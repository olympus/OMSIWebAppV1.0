<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use DB;


class Speciality extends Model
{
    use SoftDeletes;

    protected $table = 'specialities';

    protected $guarded = [];

    protected $appends = ['full_image_url'];

    /**
     * Accessor for full image url
     */

    protected static function booted()
    {
        static::deleting(function ($speciality) {

            // delete sub specialities (soft delete because model use kar raha hai)
            $subSpecialities = self::where('parent_id', $speciality->id)->get();

            foreach ($subSpecialities as $subCategory) {
                $subCategory->delete();
            }

            // soft delete related product specialities
            DB::table('product_specialities')
                ->where(function ($query) use ($speciality) {
                    $query->where('speciality_id', $speciality->id)
                          ->orWhere('sub_speciality_id', $speciality->id);
                })
                ->update([
                    'deleted_at' => now(),
                    'updated_at' => now(),
                ]);
        });
    }

    public function getFullImageUrlAttribute()
    {
        $filePath = config('image_path.file_path') ?? url('/');

        if (!empty($this->specialities_image)) {
            return $filePath . 'storage/' . ltrim($this->specialities_image, '/');
        }

        if (!empty($this->specialities_image_url)) {
            return $this->specialities_image_url;
        }

        return null;
    }
   

    // Children specialities stored in the same table via parent_id
    public function subSpecialities()
    {
        return $this->hasMany(self::class, 'parent_id', 'id')->whereNull('child_id');
    }

    public function parent()
    {
        return $this->belongsTo(self::class, 'parent_id', 'id');
    }

    /**
     * If this Speciality record links to another Speciality as its inner child, return that Speciality.
     */
    public function child()
    {
        return $this->belongsTo(self::class, 'child_id', 'id');
    }

    /**
     * Sub-subspecialities: Speciality rows that are linked to a SubSpeciality via `child_id`.
     * parent_id = this speciality id, child_id = subspeciality id
     */
    public function subSubSpecialities()
    {
        return $this->hasMany(self::class, 'parent_id', 'id')
            ->whereNotNull('child_id');
    }

    public function specialityCategories()
    {
        return $this->hasMany(SpecialityCategory::class, 'speciality_id');
    }

    public function subSpecialityData()
    {
        return $this->hasMany(Speciality::class, 'parent_id')
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
