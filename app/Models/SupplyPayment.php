<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SupplyPayment extends Model
{
    use HasFactory;

    protected $fillable = [
        'customer',
        'amount',
        'date',
        'mpesa',
        'cash',
        'expenses',
        'user',
        'store',

    ];
}
