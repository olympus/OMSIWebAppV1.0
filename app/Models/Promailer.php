<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;


class Promailer extends Model
{

    protected $table = 'promailers';

    protected $guarded = [];

    //protected $fillable = ['title', 'body'];

    protected $casts = [
        'body' => 'array',
        'created_at' => 'datetime:Y-m-d H:i:s',
        'updated_at' => 'datetime:Y-m-d H:i:s',
    ];

}
