<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ScheduleWeekly extends Model
{
    protected $table = 'schedule_weekly';

    protected $fillable = [
        'type',
        'name',
        'value',
        'time',
        'offset',
    ];
}
