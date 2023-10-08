<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class EucBoxSpare extends Model
{
    protected $fillable = [
        'euc_box_id',
        'spare_id',
        'quantity_oh',
        'quantity_replenish',
        'serial_no',
        'batch_no',
        'expiry_date',
        'charge_time',
        'calibration_due',
        'load_hydrostatic_test_due'
    ];

    public function spare ()
    {
        return $this->belongsTo('App\Models\Spare');
    }

    public function eucBox()
    {
        return $this->hasOne(EucBox::class, 'id', 'euc_box_id');
    }
}
