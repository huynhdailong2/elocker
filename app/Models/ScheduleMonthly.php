<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ScheduleMonthly extends Model
{
    protected $table = 'schedule_monthly';

    protected $fillable = [
        'type',
        'day',
        'time',
        'offset'
    ];
}
