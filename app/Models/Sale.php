<?php

namespace App\Models;

//use App\Models\Sales\NewSaleStock;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Sale extends Model
{
    use HasFactory;

    protected $fillable = [
        'user',
        'store',
        'mpesa',
        'cash',
        'bank',
        'receipt',
        'ref_number',
        'broker_total',
        'sale_total',
        'customer_name',
        'customer_phone',
        'date',
        'time',
        'deliverly_number',
        'partial_payment',
        'original_sale'

    ];

    public function user_sales(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user', 'id');
    }

    public function sale_stock(): BelongsToMany
    {
        return $this->belongsToMany(Stock::class, 'sale_products', 'sale', 'stock', "id", 'id');
    }

//    new sale stocks
    public function new_sale_stocks(): BelongsToMany
    {
        return $this->belongsToMany(\App\Models\Stock\NewStock::class, 'new_sale_stocks', 'sale', 'stock', 'id', 'id')->withPivot([
            'quantity',
            'each',
            'total',
            'broker',
            'buying_price',
            'broker_total',
            'returned_total',
            'returned',
            'serial_number',
            'replaced',
            'upgraded',
            'upgrade_to'

        ]);
    }

    //    new sale stocks cashflow summary
    public function new_sale_stocks_cashflow(): BelongsToMany
    {
        return $this->belongsToMany(\App\Models\Stock\NewStock::class, 'new_sale_stocks', 'sale', 'stock', 'id', 'id')->withPivot([
            'quantity',
            'each',
            'total',

        ]);
    }


    public function delivery()
    {
        return $this->hasOne(SaleDelivery::class, 'sale', 'id');
    }

    public function customer()
    {
        return $this->belongsToMany(Customer::class, 'customer_sales', 'sale', 'customer', 'id', 'id');
    }


//    public function new_sale_stocks(){
//        return $this->belongsToMany(NewSaleStock::class,'new_sale_stocks','sale','stock','id','id');
//
//    }
}
