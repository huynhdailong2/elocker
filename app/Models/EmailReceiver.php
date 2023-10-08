<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class EmailReceiver extends Model
{

    protected $fillable = [
        'type',
        'email'
    ];
}
