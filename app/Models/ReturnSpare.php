<?php


namespace App\Models;


use Illuminate\Database\Eloquent\Model;


/**
 * @property int bin_id
 * @property int write_off
 * @property int spare_id
 * @property int quantity
 * @property string state
 */
class ReturnSpare extends Model
{
    protected $fillable = [
        'type',
        'bin_id',
        'spare_id',
        'state',
        'quantity',
        'handover_id',
        'receiver_id',
        'quantity_returned_store',
        'noted',
        'write_off'
    ];
}
