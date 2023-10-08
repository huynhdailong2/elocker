<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TakingTransaction extends Model
{
    protected $table = 'taking_transactions';

    protected $fillable = [
        'user_id',
        'type',
        'total_qty',
        'remain_qty',
        'remain_bins',
        'job_card_id',
    ];
}
