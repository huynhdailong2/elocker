<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ReplenishmentSpareConfigure extends Model
{
    protected $fillable = [
        'replenishment_spare_id',
        'order',
        'batch_no',
        'serial_no',
        'has_charge_time',
        'charge_time',
        'has_calibration_due',
        'calibration_due',
        'has_expiry_date',
        'expiry_date'
    ];
}
