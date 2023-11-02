<?php

namespace App\Models;

use App\User;
use Illuminate\Database\Eloquent\Model;

class IssueCard extends Model
{
    protected $fillable = [
        'job_card_id',
        'bin_id',
        'euc_box_id',
        'spare_id',
        'quantity',
        'torque_wrench_area_id',
        'issuer_id',
        'taker_id',
        'returned',
        'returned_quantity',
        'taking_transaction_id'
    ];

    public function taker()
    {
        return $this->hasOne(User::class, 'id', 'taker_id');
    }
    public function bin()
    {
        return $this->belongsTo(Bin::class, 'bin_id', 'id');
    }
    public function spare()
    {
        return $this->belongsTo(Spare::class);
        // return $this->belongsToMany(Spare::class,'bin_spare', 'bin_id', 'spare_id')->withPivot(['quantity','quantity_oh', 'min', 'max', 'critical','is_processing','process_time','process_by']);
        // return $this->belongsTo(Spare::class,'bin_spare', 'bin_id', 'spare_id')->withPivot(['quantity','quantity_oh', 'min', 'max', 'critical','is_processing','process_time','process_by']);
    }
    public function torqueWrenchArea()
    {
        return $this->belongsTo(TorqueWrenchArea::class, 'torque_wrench_area_id');
    }
}
