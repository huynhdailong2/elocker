<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PolHistory extends Model
{

    protected $fillable = [
        'pol_id',
        'type',
        'quantity',
        'issuer_id',
        'receiver_id',
        'receiver_requested_id',
        'requester_id',
    ];
}
