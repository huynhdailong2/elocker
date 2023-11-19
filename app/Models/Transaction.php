<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Transaction extends Model
{
    protected $table = 'transactions';

    protected $fillable = [
        'id',
        'trans_id',
        'type',
        'status',
        'request_qty',
        'cluster_id',
        'user_id',
        'status',
    ];
    public function transactionDetails()
    {
        return $this->hasMany(TransactionDetail::class, 'transaction_id');
    }

    public function bins()
    {
        return $this->hasManyThrough(Bin::class, TransactionDetail::class, 'transaction_id', 'id', 'id', 'bin_id');
    }
    public function cluster()
    {
        return $this->belongsTo(Cluster::class);
    }
    public function shelf()
    {
        return $this->hasManyThrough(Shelf::class, TransactionDetail::class, 'transaction_id', 'id', 'id', 'shelf_id');
    }
    public function spares()
    {
        return $this->belongsToMany(Spare::class, 'transaction_details')->withPivot('job_card_id', 'quantity', 'vehicle_id', 'area_id', 'bin_id', 'shelf_id', 'conditions');
    }
    public function jobCard()
    {
        return $this->hasManyThrough(JobCard::class, TransactionDetail::class, 'transaction_id', 'id', 'id', 'job_card_id');
    }
    public function vehicle()
    {
        return $this->hasManyThrough(Vehicle::class, TransactionDetail::class, 'transaction_id', 'id', 'id', 'vehicle_id');
    }
    public function torqueWrenchArea()
    {
        return $this->hasManyThrough(TorqueWrenchArea::class, TransactionDetail::class, 'transaction_id', 'id', 'id', 'area_id');
    }
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
