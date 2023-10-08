<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

use App\Traits\GenerateSql;

class PolManagement extends Model
{
    use GenerateSql, SoftDeletes;

    protected $table = 'pol_managements';

    protected $fillable = [
        'card_number',
        'material_number',
        'purpose_use',
        'description',
        'type',
        'request_date',
        'request_quantity',
        'received_date',
        'received_quantity',
        'issued_date',
        'issued_quantity',
        'expiry_date',
        'status',
        'request_by',
        'auditor'
    ];
}
