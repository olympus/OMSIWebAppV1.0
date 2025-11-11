<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;


class EmployeeTeam extends Model
{
  //
    protected $table = 'employee_team';
    protected $guarded = [];
    
    public function service_requests()
    {
        return $this->hasMany(ServiceRequests::class, 'employee_code', 'employee_code');
    }

    public static function getEmployee($employee_code)
    {
        return EmployeeTeam::where('employee_code', $employee_code)->first();
    }

    public static function getNameImage($employee_code)
    {
        return EmployeeTeam::select('employee_code','name','image')->where('employee_code', $employee_code)->first();
    }

    protected $casts = [
        'created_at' => 'datetime:Y-m-d H:i:s',
        'updated_at' => 'datetime:Y-m-d H:i:s',
    ];
}
