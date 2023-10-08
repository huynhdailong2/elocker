<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Cluster extends Model
{
    protected $fillable = [
        'name',
        'code',
        'is_rfid',
        'is_virtual',
    ];
}
