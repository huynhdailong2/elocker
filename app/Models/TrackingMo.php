<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TrackingMo extends Model
{
    protected $table = 'tracking_mo';

    protected $fillable = [
        'job_card_id',
        'bin_id',
        'euc_box_id',
        'spare_id',
        'quantity',
        'torque_wrench_area_id',
        'issuer_id',
        'taker_id',
        'returned',
        'returned_quantity',
        'issue_card_id',
    ];
}
