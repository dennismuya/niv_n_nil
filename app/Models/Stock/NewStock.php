<?php

namespace App\Models\Stock;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class NewStock extends Model
{
    use HasFactory;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'new_stocks';


    protected $fillable = [
        'stock_name',
        'stock_properties',
        'product_id',
        'nakuru_quantity',
        'old_nation_quantity',
        'chini_ya_mnazi_quantity',
        'selling_price',
        'available stock',
        'SKU'
    ];

    public function stock_history_debt(){
        return $this->hasMany(StockHistory::class,'stock','id')->where('action',11);
    }




}
