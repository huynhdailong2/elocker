<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ReplenishEucBox extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'euc_box_id',
        'spare_id',
        'is_confirmed',
        'requester_id', // bring euc item for the storeman
        'receiver_id'
    ];
}
