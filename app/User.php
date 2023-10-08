<?php

namespace App;

use Illuminate\Notifications\Notifiable;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Laravel\Passport\HasApiTokens;
use App\Traits\GenerateSql;
use App\Consts;
use Illuminate\Database\Eloquent\SoftDeletes;

class User extends Authenticatable
{
     use HasApiTokens, Notifiable, GenerateSql, SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'email', 'name', 'login_name', 'card_id', 'employee_id', 'password', 'role', 'dept', 'avatar', 'updated_by',
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'remember_token',
    ];

    public function getRoleNameAttribute()
    {
        return $this->attributes['role_name'] ?? '';
    }

    protected $appends = ['role_name'];

    public function setPasswordAttribute($pass)
    {
        $this->attributes['password'] = bcrypt($pass);
    }

    public function findForPassport($loginId) {
        return $this->where('login_name', $loginId)->first();
    }

    public function isAdministrator()
    {
        return $this->role === Consts::USER_ROLE_ADMINISTRATOR || $this->role === Consts::USER_ROLE_SUPER_ADMIN
            || $this->role === Consts::USER_ROLE_ADMIN_SUPPORT;
    }

    public function isSuperAdmin()
    {
        return $this->role === Consts::USER_ROLE_SUPER_ADMIN;
    }
}
