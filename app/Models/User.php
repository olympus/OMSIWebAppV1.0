<?php

namespace App\Models;

use Filament\Models\Contracts\FilamentUser;
use Filament\Panel;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\HasDatabaseNotifications;
use Illuminate\Notifications\Notifiable;
use Spatie\Permission\Traits\HasRoles;

//use Laratrust\Traits\LaratrustUserTrait;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Models\Activity;

class User extends Authenticatable implements FilamentUser
{
 //
  //  use LaratrustUserTrait;
    use Notifiable;
    use HasRoles;
    use HasDatabaseNotifications;
    use LogsActivity;
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name', 'email', 'password', 'is_expired',
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'password', 'remember_token',
    ];

    protected static $logFillable = true;

    public function adminPasswordHistories()
    {
        return $this->hasMany('App\AdminPasswordHistory');
    }

    public function canAccessPanel(Panel $panel): bool
    {
        return true; // You can customize this logic based on your requirements
    }

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()->logAll();
    } 

    public function activities()
    {
        return $this->hasMany(Activity::class, 'subject_id');
    }
}
