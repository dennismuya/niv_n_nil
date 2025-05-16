<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Slip extends Model
{
    use HasFactory;

    protected $fillable = [
        'slipped_by',
        'slip_date',
        'supply_amount_before',
        'debt_owed_before',
        'slip_total',
        'store',
        'customer',
        'slip_verified',
        'comment',

    ];

}
