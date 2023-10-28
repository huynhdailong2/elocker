<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Traits\GenerateSql;
use Illuminate\Database\Eloquent\SoftDeletes;

class WriteOff extends Model
{
    use GenerateSql, SoftDeletes;

    protected $table='write_offs';
    protected $fillable = [
        'return_spare_id',
        'bin_id',
        'spare_id',
        'user_id',
        'quantity',
        'reason',
        'eliminator_id',
        'cluster_name',
        'cabinet_name',
        'bin_name',
    ];
    public function bin()
    {
        return $this->belongsTo(Bin::class,'bin_id');
    }
    public function spares()
    {
        return $this->belongsTo(Spare::class,'spare_id');
    }
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
