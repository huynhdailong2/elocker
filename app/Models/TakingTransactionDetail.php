<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TakingTransactionDetail extends Model
{
    protected $table = 'taking_transaction_details';

    protected $fillable = [
        'taking_transaction_id',
        'spare_id',
        'job_card_id',
        'vehicle_id',
        'job_name',
        'vehicle_num',
        'area_id',
        'area_name',
        'platform',
        'listWO',
        'request_qty',
        'cabinet_id',
        'bin_id',
    ];
    protected $hidden = [
        'listWO'
        
    ];
}
