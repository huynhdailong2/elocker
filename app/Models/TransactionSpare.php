<?php


namespace App\Models;


use Illuminate\Database\Eloquent\Model;

class TransactionSpare extends Model
{
    protected $table = 'spare_taking_transaction';
    protected $fillable = [
        'taking_transaction_id',
        'spare_id',
    ];
    // public $timestamps = true;
}
