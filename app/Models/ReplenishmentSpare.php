<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ReplenishmentSpare extends Model
{

    protected $fillable = [
        'replenishment_id',
        'bin_id',
        'spare_id',
        'quantity'
    ];

    public function spare()
    {
        return $this->belongsTo('App\Models\Spare');
    }

    public function binInfo()
    {
        return $this->belongsTo('App\Models\Bin', 'bin_id', 'id')
            ->join('clusters', 'clusters.id', 'bins.cluster_id')
            ->join('shelfs', 'shelfs.id', 'bins.shelf_id')
            ->where('bins.is_failed', 0)
            ->select('bins.*', 'shelfs.name as shelf_name', 'clusters.name as cluster_name');
    }
}
