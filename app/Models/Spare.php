<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Spare extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'name',
        'part_no',
        'material_no',
        'location',
        'supplier_email',
        'mat_grp',
        'cricode',
        'jom',
        'item_acct',
        'type',
        'has_batch_no',
        'has_serial_no',
        'has_charge_time',
        'has_calibration_due',
        'has_expiry_date',
        'has_load_hydrostatic_test_due',
        'field1',
        'field2',
        'url',
        'description',
        'auditor'
    ];
    protected $hidden = [
        'location'
    ];

    public function userAccessingSpares()
    {
        return $this->hasMany('App\Models\UserAccessingSpare');
    }

    public function setHasBatchNoAttribute($value)
    {
        $this->attributes['has_batch_no'] = filter_var($value, FILTER_VALIDATE_BOOLEAN) ? 1 : 0;
    }

    public function setHasSerialNoAttribute($value)
    {
        $this->attributes['has_serial_no'] = filter_var($value, FILTER_VALIDATE_BOOLEAN) ? 1 : 0;
    }

    public function setHasChargeTimeAttribute($value)
    {
        $this->attributes['has_charge_time'] = filter_var($value, FILTER_VALIDATE_BOOLEAN) ? 1 : 0;
    }

    public function setHasCalibrationDueAttribute($value)
    {
        $this->attributes['has_calibration_due'] = filter_var($value, FILTER_VALIDATE_BOOLEAN) ? 1 : 0;
    }

    public function setHasExpiryDateAttribute($value)
    {
        $this->attributes['has_expiry_date'] = filter_var($value, FILTER_VALIDATE_BOOLEAN) ? 1 : 0;
    }

    public function setHasLoadHydrostaticTestDueAttribute($value)
    {
        $this->attributes['has_load_hydrostatic_test_due'] = filter_var($value, FILTER_VALIDATE_BOOLEAN) ? 1 : 0;
    }
    public function bins()
    {
        return $this->belongsToMany(Bin::class);
    }
    public function binconfigure()
    {
        return $this->belongsToMany(BinConfigure::class);
    }
}
