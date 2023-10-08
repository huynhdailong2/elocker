<?php


namespace App\Models;


use Jenssegers\Mongodb\Eloquent\Model as Eloquent;


class WeighingTransaction extends Eloquent
{
    protected $connection = 'mongodb';

    protected $collection = 'weighing_transactions';

    protected $guarded = [];

    public function weighingHistory()
    {
        return $this->hasOne(WeighingHistory::class, '_id', 'weighing_history_id');
    }
}
