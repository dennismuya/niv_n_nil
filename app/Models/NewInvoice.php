<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class NewInvoice extends Model
{
    use HasFactory;

    protected $fillable = [
        'customer',
        'store',
        'user',
        'stock',
        'old_stock',
        'date',
        'price',
        'quantity',
        'total',
        'returned',
        'returned_date',
        'returned_by',
        'picked_by',
    ];

    public  function  stock(){
        return $this->belongsTo(Stock::class,'stock','id');
    }
    public function  old_stock(){
        return $this->belongsTo(OldStock::class,'old_stock','id');
    }
}
