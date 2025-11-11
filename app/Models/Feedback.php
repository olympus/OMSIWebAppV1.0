<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Feedback extends Model
{
	//
    protected $table = 'feedback';
    protected $guarded = [];
    public function ServiceRequestData()
    {
        return $this->hasOne(ServiceRequests::class, 'feedback_id' ,'id');
    }

    public function ArchiveServiceRequestData()
    {
        return $this->hasOne(ArchiveServiceRequests::class, 'feedback_id' ,'id');
    }

    protected $casts = [
        'created_at' => 'datetime:Y-m-d H:i:s',
        'updated_at' => 'datetime:Y-m-d H:i:s',
    ];
}
