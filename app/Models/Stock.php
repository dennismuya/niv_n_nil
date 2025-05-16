<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Stock extends Model
{
    use HasFactory;

    protected  $fillable =[
        'properties',
        'store',
        'product',
        'price',
        'buying_price',
        'serial',
        'sold',
        'user',
        'stock_name',
        'ready',
        'stock_quantity'

  ];


    protected $casts = [
        'stock_quantity'=>'integer'

    ];

    public function product():BelongsTo{
        return $this->belongsTo(Product::class,'product','id');
    }

    public function store():BelongsTo{
        return $this->belongsTo(Store::class,'store','id');
    }

    public function supplier():BelongsToMany{
        return $this->belongsToMany(Customer::class,'customer_supplies','stock','customer','id','id');
    }

    public function invoice():BelongsToMany{
        return $this->belongsToMany(Invoice::class,'invoice_products','stock','invoice','id','id');
    }


}
