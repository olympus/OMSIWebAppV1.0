<?php

namespace App;

use Illuminate\Database\Eloquent\Model;


class Promailer extends Model
{

    protected $table = 'promailers';

    protected $casts = [
        'created_at' => 'datetime:Y-m-d H:i:s',
        'updated_at' => 'datetime:Y-m-d H:i:s',
    ];

}
