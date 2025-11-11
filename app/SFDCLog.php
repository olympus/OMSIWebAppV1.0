<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class SFDCLog extends Model
{
    protected $table = 'sfdc_logs';
    protected $fillable = [
        'request_id',
        'previous_status',
        'new_status',
        'splits',
        'employee_code',
        'action',
        'response'
    ];
}
