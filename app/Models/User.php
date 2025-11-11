<?php

namespace App\Models;

use Filament\Models\Contracts\FilamentUser;
use Filament\Panel;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\HasDatabaseNotifications;
use Illuminate\Notifications\Notifiable;
use Spatie\Permission\Traits\HasRoles;

//use Laratrust\Traits\LaratrustUserTrait;

class User extends Authenticatable implements FilamentUser
{
 //
  //  use LaratrustUserTrait;
    use Notifiable;
    use HasRoles;
    use HasDatabaseNotifications;

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
}
