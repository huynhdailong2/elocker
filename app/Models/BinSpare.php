<?php


namespace App\Models;


use Illuminate\Database\Eloquent\Model;

class BinSpare extends Model
{
    protected $fillable = [
        'bin_id',
        'spare_id',
        'quantity',
        'quantity_oh',
        'min',
        'max',
    ];
    public $timestamps = true;
}
