<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Reportsetting extends Model
{
    protected $fillable = [
        'name', 'to_emails','cc_emails'
    ];
}
