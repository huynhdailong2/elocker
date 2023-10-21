<?php


namespace App\Models;


use Illuminate\Database\Eloquent\Model;

class TransactionDetails extends Model
{
    protected $table = 'taking_transaction_details';
    protected $fillable = [
        'taking_transaction_id',
        'spare_id',
        'listWO',
    ];
    // public $timestamps = true;
}
