<?php

namespace App\Models;

use App\User;
use Illuminate\Database\Eloquent\Model;

class IssueCard extends Model
{
    protected $fillable = [
        'job_card_id',
        'bin_id',
        'euc_box_id',
        'spare_id',
        'quantity',
        'torque_wrench_area_id',
        'issuer_id',
        'taker_id',
        'returned',
        'returned_quantity'
    ];

    public function taker()
    {
        return $this->hasOne(User::class, 'id', 'taker_id');
    }
}
