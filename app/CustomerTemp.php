<?php

namespace App;

use App\Models\Video;
use Illuminate\Database\Eloquent\Model;

use Tymon\JWTAuth\Contracts\JWTSubject;

class CustomerTemp extends Model implements JWTSubject
{

    // $customer = factory(App\Customer::class)->make();

    protected $table = 'customer_temps';

    protected $guarded = [];

    public function isEmployee($id)
    {
        $customer = $this->findOrFail($id);
        if(strpos($customer->email, '@olympus.com')){
            return true;
        }else{
            return false;
        }
    }

    public function videos()
    {
        return $this->hasMany(Video::class);
    }

    public function getJWTIdentifier()
    {
        return $this->getKey();
    }

    public function getAuthIdentifierName()
    {
        return $this->getKeyName();
    }

    public function getJWTCustomClaims()
    {
        return [];
    }

    public function passwordHistories()
    {
        return $this->hasMany('App\PasswordHistory');
    }
}
