<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class Video extends Model
{
    protected $appends = ['created_at_readable'];
    protected $withCount = ['customers'];
    protected $guarded = [];
    // protected static function boot()
    // {
    //     parent::boot();
    //     static::addGlobalScope('enabled', function (Builder $builder) {
    //         $builder->where('enabled', 1);
    //     });
    // }

    public function getCreatedAtReadableAttribute()
    {
        return Carbon::createFromFormat('Y-m-d H:i:s', $this->attributes['created_at'])->diffForHumans();
    }

    public function customers()
    {
        return $this->belongsToMany(Customers::class, 'customer_video', 'video_id', 'customer_id')->withPivot('created_at');
    }

    public function getCreatedAtAttribute($value)
    {
        $this->getCreatedAtReadableAttribute();
        return $value;
    }

    public function viewedAt()
    {
        return $this->pivot->created_at;
    }
}
