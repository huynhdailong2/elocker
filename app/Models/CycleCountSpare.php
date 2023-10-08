<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CycleCountSpare extends Model
{
    protected $fillable = [
        'cycle_count_id',
        'spare_id',
        'count'
    ];
}
