<?php


namespace App\Models;


use Jenssegers\Mongodb\Eloquent\Model as Eloquent;


class WeighingHistory extends Eloquent
{
    protected $connection = 'mongodb';

    protected $collection = 'weighing_histories';

    protected $guarded = [];
}
