<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BinConfigure extends Model
{
    protected $fillable = [
        'bin_id',
        'spare_id',
        'order',
        'batch_no',
        'serial_no',
        'has_charge_time',
        'charge_time',
        'has_calibration_due',
        'calibration_due',
        'has_expiry_date',
        'expiry_date',
        'has_load_hydrostatic_test_due',
        'load_hydrostatic_test_due'
    ];
    // public function bins()
    // {
    //     return $this->belongsToMany(Bin::class);
    // }
    public function spares()
    {
        return $this->belongsToMany(Spare::class);
    }
}
