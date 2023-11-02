<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TrackingMo extends Model
{
    protected $table = 'tracking_mo';

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
        'returned_quantity',
        'issue_card_id',
    ];
    public function torqueWrenchArea()
    {
        return $this->belongsTo(TorqueWrenchArea::class, 'torque_wrench_area_id');
    }
    public function issueCard()
    {
        return $this->belongsTo(IssueCard::class, 'issue_card_id');
    }
    public function jobCard()
    {
        return $this->belongsTo(JobCard::class, 'job_card_id')->with('vehicle');
    }
}
