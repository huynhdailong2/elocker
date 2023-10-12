<?php


namespace App\Models;


use Illuminate\Database\Eloquent\Model;

/**
 * @property int spare_id
 * @property ?int quantity
 * @property ?int quantity_oh
 * @property ?int min
 * @property ?int max
 * @property ?int critical
 * @property string status
 */
class Bin extends Model
{
    protected $fillable = [
        'cluster_id',
        'shelf_id',
        'row',
        'bin',
        'drawer_name',
        // 'spare_id',
        'quantity',
        'quantity_oh',
        'status',
        'min',
        'max',
        'critical',
        'description',
        'is_drawer',
        'is_failed',
        'is_processing',
        'process_time',
        'process_by',
    ];

    public function configures()
    {
        return $this->hasMany('App\Models\BinConfigure');
    }

    public function spares()
    {
        return $this->belongsToMany(Spare::class);
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
