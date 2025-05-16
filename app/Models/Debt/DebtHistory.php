<?php

namespace App\Models\Debt;

use App\Models\Customer;
use App\Models\OldStock;
use App\Models\Stock\NewStock;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DebtHistory extends Model
{
    use HasFactory;

    protected $table = 'debt_histories';
    protected $fillable = [
        'stock',
        'old_stock',
        'user',
        'customer',
        'date',
        'pick',
        'return',
        'quantity',
        'serial_number',
        'price',
        'total_amount',
        'returned_by',
        'store',
        'returned_quantity',
        'sale',
        'debt',
        'lot_total',
        'invoice_lot',
        'upgraded_stock_name'
    ];

    public function customer()
    {
        return $this->belongsTo(Customer::class, 'customer', 'id');
    }

    public function old_stock()
    {
        return $this->belongsTo(OldStock::class, 'old_stock', 'id');

    }

    public function stock()
    {
        return $this->belongsTo(NewStock::class, 'stock', 'id');
    }

    public function debt_history_stocks()
    {
        return $this->belongsTo(NewStock::class, 'stock', 'id');
    }


}
