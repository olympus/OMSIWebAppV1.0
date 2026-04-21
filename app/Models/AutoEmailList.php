<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AutoEmailList extends Model
{
    protected $table = 'autoemaillist';

    protected $fillable = [
        'request_type',
        'sub_type',
        'states',
        'departments',
        'to_emails',
        'cc_emails',
        'escalation_1',
        'escalation_2',
        'escalation_3',
        'escalation_4'
    ];
    
}
