<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use DateTimeInterface;


class AdminPasswordHistory extends Model
{

    protected $fillable = ['user_id','password'];
}
