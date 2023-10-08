<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Vehicle extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'name',
        'vehicle_num',
        'vehicle_type_id',
        'variant',
        'unit',
        'unit_other',
        'mileage_start',
        'mileage_end',
        't_loan',
        'unserviceable',
        'last_point_servicing',
        'schedule_6_months',
        'completion_date_6_months',
        'schedule_12_months',
        'completion_date_12_months',
        'schedule_18_months',
        'completion_date_18_months',
        'schedule_24_months',
        'completion_date_24_months',
        'status',
        'is_active',
    ];
}
