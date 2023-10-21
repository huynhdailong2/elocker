<?php


namespace App\Models;


use Illuminate\Database\Eloquent\Model;

class TransactionSpare extends Model
{
    protected $table = 'takting_transaction_detail';
    protected $fillable = [
        'taking_transaction_id',
        'spare_id',
        'listWO',
    ];
    // public $timestamps = true;
}
