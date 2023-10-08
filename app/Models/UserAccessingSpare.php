<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UserAccessingSpare extends Model
{
    protected $fillable = [
        'spare_id',
        'role'
    ];
}
