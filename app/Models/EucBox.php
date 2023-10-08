<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class EucBox extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'order',
        'vehicle_type_id',
        'platform',
        'is_active',
    ];

    public function spares()
    {
        return $this->hasMany('App\Models\EucBoxSpare')->with('spare');
    }
}
