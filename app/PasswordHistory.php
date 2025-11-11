<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use DateTimeInterface;
use App\Helpers\SMS; 

class PasswordHistory extends Model
{
    protected $fillable = ['customer_id','password_one','password_two','password_three','password_four','password_five']; 
}
