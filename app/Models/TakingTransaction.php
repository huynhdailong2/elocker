<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TakingTransaction extends Model
{
    protected $table = 'taking_transactions';

    protected $fillable = [
        'hardware_port',
        'name',
        'part_number',
        'port_id',
        'total_qty',
        'qty',
        'status',
        // 'item_id',
        'pre_qty',
        'changed_qty',
        'user_id',
        'type',
        'remain_qty',
        'bin_id',
        'cabinet_id',
        'request_qty',
        'cluster_name',
        'cabinet_name',
        'bin_name',
        // 'spare_id',
    ];
    protected $hidden = [
        'remain_qty',
        'total_qty',
        'hardware_port',
        'part_number',
        'port_id',
        'qty',
        'pre_qty',
        'changed_qty',
        'item_id',
        'spares',
        'bin',
        'cabinet',
        
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
        return $this->belongsTo(Bin::class, 'bin_id');
    }
    public function cabinet()
    {
        return $this->belongsTo(Shelf::class, 'cabinet_id');
    }
    public function spares()
    {
        return $this->belongsToMany(Spare::class, 'taking_transaction_details')->withPivot('job_card_id','job_name', 'vehicle_id','vehicle_num','area_id','area_name', 'platform');
    }
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
