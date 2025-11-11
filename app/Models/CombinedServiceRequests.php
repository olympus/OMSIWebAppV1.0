<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class CombinedServiceRequests extends Model
{
    // protected $table = 'service_requests'; // default for editing

    // public static function unifiedQuery(): Builder
    // {
    //     $active = ServiceRequests::query()->select('*');
    //     $archive = ArchiveServiceRequests::query()->select('*');

    //     return $active->unionAll($archive);
    // } 
    protected $table = 'combined_service_requests'; // virtual alias
    public $timestamps = false;

    //protected $table = 'service_requests'; // dummy
    //public $timestamps = false;
    public $incrementing = false;
    protected $guarded = [];

    // ✅ Keep relationships so Filament columns still work
    public function customer()
    {
        return $this->belongsTo(\App\Models\Customers::class, 'customer_id');
    }

    public function hospital()
    {
        return $this->belongsTo(\App\Models\Hospitals::class, 'hospital_id');
    }

    public function departmentData()
    {
        return $this->belongsTo(\App\Models\Departments::class, 'dept_id');
    }

    public function employeeData()
    {
        return $this->belongsTo(\App\Models\EmployeeTeam::class, 'employee_code', 'employee_code');
    }

    public function getStatusTimelineDataAttribute()
    {
        return $this->attributes['status_timeline_data'] ?? [];
    } 

}
