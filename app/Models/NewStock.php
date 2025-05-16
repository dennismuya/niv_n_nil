<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class NewStock extends Model
{
    use HasFactory;

    protected $fillable = [
        'stock_name',
        'stock_properties',
        'nakuru_quantity',
        'old_nation_quantity',
        'chini_ya_mnazi_quantity',
        'selling_price',
        'available stock',
        'SKU',

    ];


}
