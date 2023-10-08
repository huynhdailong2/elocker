<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SystemLog extends Model
{
    protected $fillable = [
        'action',
        'type',
        'query',
        'data',
    ];

    public static function getTableName()
    {
        return with(new static)->getTable();
    }
}
