<?php


namespace App\Models;


use Illuminate\Database\Eloquent\Model;

class TransactionSpare extends Model
{
    protected $table = 'taking_transaction_details';
    protected $fillable = [
        'taking_transaction_id',
        'spare_id',
        'listWO',
        'job_card_id',
        'vehicle_id',
        'platform',
        'job_name',
        'vehicle_num',
        'area_id',
        'area_name',
        'request_qty',
        'cabinet_id',
        'bin_id',
        'location',
    ];
    protected $hidden = ['listWO'];
    public function job_card()
    {
        return $this->belongsTo(JobCard::class, 'job_card_id');
    }

    // public $timestamps = true;
}
