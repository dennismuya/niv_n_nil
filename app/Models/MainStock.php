<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MainStock extends Model
{
    use HasFactory;

    protected $fillable = [
        'id',
        'store',
        'product',
        'properties',
        'price',
        'deleted',
        'deleted_by',
        'received',
        'transferred_date',
        'returned',
        'returned_date',
        'transferred',
        'received_by',
        'transferred_by',
        'buying_price',
        'serial',
        'sold',
        'broker',
    ];


}
