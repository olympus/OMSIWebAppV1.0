<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use DateTimeInterface; 

class PasswordReset extends Model
{
    protected $fillable = ['email','token','created_at']; 
}
