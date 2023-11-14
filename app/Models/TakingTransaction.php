<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TakingTransaction extends Model
{
    protected $table = 'taking_transactions';

    protected $fillable = [
        'id',
        'status',
        'user_id',
        'type',
        'name',
        'remain_qty',
        'bin_id',
        'cabinet_id',
        'request_qty',
        'cluster_name',
        'cabinet_name',
        'job_card_id',
        'bin_name',
        'spare_id',
        'total_qty',
        'remain_qty',
    ];
    protected $hidden = [
        'total_qty',
        'remain_qty',
        'hardware_port',
        'part_number',
        'port_id',
        'qty',
        'pre_qty',
        'changed_qty',
        'spares',
        'bin',
        'cabinet',
        'request_qty',
        
    ];
    protected $appends = ['locations'];
    public function getLocationsAttribute()
    {
        $bin = $this->bin;
        $cabinet = $this->cabinet;
        $spares = $this->spares;
        return [
            'bin' => $bin,
            'cabinet' => $cabinet,
            'spares' => $spares,
        ];
    }
    public function bin()
    {
        return $this->belongsTo(Bin::class,  'taking_transaction_details', 'bin_id', 'id');
    }
    public function cabinet()
    {
        return $this->belongsTo(Shelf::class, 'taking_transaction_details','cabinet_id','id');
    }
    public function spares()
    {
        return $this->belongsToMany(Spare::class, 'taking_transaction_details')->withPivot('job_card_id','job_name','request_qty', 'vehicle_id','vehicle_num','area_id','area_name', 'platform','bin_id','cabinet_id','location');
    }
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
