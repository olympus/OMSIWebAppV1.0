<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;


class StatusTimeline extends Model
{

    protected $dates = ['created_at','updated_at'];

    protected $guarded = [];


    protected $casts = [
        'created_at' => 'datetime:Y-m-d H:i:s',
        'updated_at' => 'datetime:Y-m-d H:i:s',
    ];
}
