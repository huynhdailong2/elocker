<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SysAPILog extends Model
{
    protected $table = 'sys_api_logs';

    protected $fillable = [
        'method',
        'url',
        'request',
        'response',
        'host_ip',
        'user_agent',
        'request_date',
    ];
}
