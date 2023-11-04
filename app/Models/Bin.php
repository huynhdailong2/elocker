<?php


namespace App\Models;


use Illuminate\Database\Eloquent\Model;

// /**
//  * @property int spare_id
//  * @property ?int quantity
//  * @property ?int quantity_oh
//  * @property ?int min
//  * @property ?int max
//  * @property ?int critical
//  * @property string status
//  */
class Bin extends Model
{
    protected $fillable = [
        'cluster_id',
        'shelf_id',
        'row',
        'bin',
        'drawer_name',
        'spare_id',
        'quantity',
        'quantity_oh',
        'status',
        'min',
        'max',
        'critical',
        'description',
        'is_drawer',
        'is_failed',
        'cu_id',
        'lock_id',
    ];
    protected $hidden =[
        'is_processing',
        'process_time',
        'process_by',
    ];
    public function configures()
    {
        return $this->hasMany(BinConfigure::class);
    }

    public function spares()
    {
        return $this->belongsToMany(Spare::class,'bin_spare', 'bin_id', 'spare_id')->withPivot(['quantity','quantity_oh', 'min', 'max', 'critical','is_processing','process_time','process_by']);
    }

    public function cluster()
    {
        return $this->belongsTo(Cluster::class);
    }

    public function shelf()
    {
        return $this->belongsTo(Shelf::class);
    }
}
