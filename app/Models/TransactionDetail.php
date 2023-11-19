<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TransactionDetail extends Model
{
    protected $table = 'transaction_details';

    protected $fillable = [
        'transaction_id',
        'shelf_id',
        'row',
        'bin_id',
        'spare_id',
        'job_card_id',
        'vehicle_id',
        'area_id',
        'quantity',
        'conditions',
    ];
    public function jobCard()
    {
        return $this->belongsTo(JobCard::class, 'job_card_id');
    }
    public function vehicle()
    {
        return $this->belongsTo(Vehicle::class, 'vehicle_id');
    }
    public function torqueWrenchArea()
    {
        return $this->belongsTo(TorqueWrenchArea::class, 'area_id');
    }
    public function transaction()
    {
        return $this->belongsTo(Transaction::class)->with('cluster', 'user');
    }
    public function spares()
    {
        return $this->belongsTo(Spare::class, 'spare_id');
    }
    public function bin()
    {
        return $this->belongsTo(Bin::class, 'bin_id')->with('configures','spares');
    }
    public function shelf()
    {
        return $this->belongsTo(Shelf::class, 'shelf_id');
    }
    
}
