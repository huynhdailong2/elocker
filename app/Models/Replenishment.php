<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Replenishment extends Model
{

    protected $fillable = [
        'uuid',
        'type',
        'created_by',
        'updated_by',
    ];

    public function replenishSpares()
    {
        return $this->hasMany('App\Models\ReplenishmentSpare')
            ->with(['binInfo', 'spare']);
    }
}
