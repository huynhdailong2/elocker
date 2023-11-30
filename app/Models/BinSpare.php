<?php


namespace App\Models;


use Illuminate\Database\Eloquent\Model;

class BinSpare extends Model
{
    protected $table = 'bin_spare';
    protected $fillable = [
        'bin_id',
        'spare_id',
        'quantity',
        'quantity_oh',
        'min',
        'max',
        'is_processing',
        'process_time',
        'process_by',
    ];
    public function spares()
    {
        return $this->belongsTo(Spare::class, 'spare_id');
    }
    public function bin()
    {
        return $this->belongsTo(Bin::class, 'bin_id')->with('cluster', 'shelf','configures');
    }

    public $timestamps = true;
}
