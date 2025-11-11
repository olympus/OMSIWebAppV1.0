<?php

namespace App\Models;
use App\Models\Departments;
use App\StatusTimeline;
use Cache;
use DB;
use Illuminate\Database\Eloquent\Model;

//

class ServiceRequests extends Model
{
	//
    protected $table = 'service_requests';

    protected $guarded = [];

    protected static $logFillable = true;

    protected function isAssigned($id)
    {
        $employee_code = $this->find($id)->value('employee_code');
        if(is_null($employee_code)){
            return false;
        }else{
            return true;
        }
    }

    public function timelines()
    {
        return $this->hasMany(StatusTimeline::class, 'request_id');
    }

    public function employee()
    {
        return $this->belongsTo(EmployeeTeam::class);
    }

    public function employeeData()
    {
        return $this->belongsTo(EmployeeTeam::class, 'employee_code' ,'employee_code');
    }

    public function hospital()
    {
        return $this->belongsTo(Hospitals::class);

    }

    public function departmentData(){
        return $this->belongsTo(Departments::class, 'dept_id' ,'dept_id');
    }

    public function customer()
    {
        return $this->hasOne(Customers::class,'id','customer_id');

    }

    public function statusTimelineData()
    {
        return $this->hasMany(StatusTimeline::class,'request_id','id');

    }

    public function get_receive_time(){
        return $this->statusTimelineData->where('status', 'received')->first();
    }

    protected $casts = [
        'created_at' => 'datetime:Y-m-d H:i:s',
        'updated_at' => 'datetime:Y-m-d H:i:s',
    ];

}
