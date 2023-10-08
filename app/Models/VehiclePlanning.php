<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class VehiclePlanning extends Model
{
    protected $fillable = [
        'vehicle_id',
        'total_aa',
        'hub',
        'unit',
        'canvas',
        'current_tos',
        'schedule_date',
        'pm_order',
        'pm_date_opened',
        'pm_date_closed',
        'pm_date_completed',
        'bom_spare_date_received',
        'car_park',
        'block',
        'level'
     ];
}
