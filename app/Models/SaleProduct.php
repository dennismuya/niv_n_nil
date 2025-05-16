<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SaleProduct extends Model
{
    use HasFactory;
    protected $fillable = [
        'sale',
        'stock',
        'selling_price',
        'selling_properties',
        'broker',

    ];
}
