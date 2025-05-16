<?php

namespace App\Models\Stock;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class StockHistory extends Model
{
    use HasFactory;

    protected $fillable = [
        'stock',
        'stock_action',
        'buying_price',
        'selling_price',
        'quantity',
        'supplier',
        'action_date',
        'user',
        'store',
        'previous_stock',
        'stock_after',
        'reason',
        'serial_number',
        'serial_array',
        'returned_from',
        'replaced',
        'sale_replaced',

    ];


}
