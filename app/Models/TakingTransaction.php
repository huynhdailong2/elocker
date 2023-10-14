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
        'item_id',
        'pre_qty',
        'changed_qty',
        'user_id',
        'type',
        'remain_qty',
        'bin_id',
        'cabinet_id',
        'spare_id',
    ];
    protected $hidden = [
        'remain_qty'
    ];
    protected $appends = ['location'];

    public function getLocationAttribute()
    {
        $bin = $this->bin;
        $cabinet = $this->cabinet;
        return [
            'bin' => $bin,
            'cabinet' => $cabinet,
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
    public function spare()
    {
        return $this->belongsTo(Spare::class, 'spare_id');
    }
    // public function searchSpare(){
    //     return $this->hasMany(Spare::class, 'spare_id');
    // }
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
    
}
