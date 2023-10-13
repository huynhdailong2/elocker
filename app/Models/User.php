<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class User extends Model
{
    protected $table = 'users';

    protected $fillable = [
        'login_name',
        'role',
    ];
    protected $hidden =[
        'email',
        'card_id',
        'employee_id',
        'password',
        'dept',
        'avatar',
        'remember_token',
    ];
    

}
