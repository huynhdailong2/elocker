<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Traits\GenerateSql;
use Illuminate\Database\Eloquent\SoftDeletes;

class WriteOff extends Model
{
    use GenerateSql, SoftDeletes;

    protected $fillable = [
        'return_spare_id',
        'bin_id',
        'spare_id',
        'user_id',
        'quantity',
        'reason',
        'eliminator_id'
    ];
}
