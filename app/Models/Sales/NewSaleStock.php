<?php

namespace App\Models\Sales;

use App\Models\Stock\NewStock;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class NewSaleStock extends Model
{
    use HasFactory;

    protected $table = 'new_sale_stocks';

    protected $fillable = [
        'sale',
        'stock',
        'quantity',
        'each',
        'broker',
        'returned_total',
        'returned',
        'total',
        'replaced',
        'serial_number',
        'serial_array',
        'store',
        'date',
        'upgraded',
        'upgrade_to'

    ];



    public function  new_stocks(){
        return $this->belongsTo(NewStock::class,'stock','id');
    }


}
