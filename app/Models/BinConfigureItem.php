<?php


namespace App\Models;


use Illuminate\Database\Eloquent\Model;

class BinConfigureItem extends Model
{
    protected $fillable = [
        'bin_configure_id',
        'bin_id',
    ];
    public $timestamps = true;
}
