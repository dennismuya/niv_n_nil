<?php

namespace App\Models\Stock;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SupplierHistory extends Model
{
    use HasFactory;
    protected $fillable = [
        'stock',
        'quantity',
        'supplier',
        'buying_price',
        'total_price',
        'supply_date',
        'received_by',
        'serial_number',
        'returned'


    ];
}
