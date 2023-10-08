<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Shelf extends Model
{
    protected $table = 'shelfs';

    protected $fillable = [
        'cluster_id',
        'name',
        'code',
        'num_rows',
        'num_bins',
        'type',
    ];
}
