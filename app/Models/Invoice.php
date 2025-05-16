<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Invoice extends Model
{
    use HasFactory;

    protected $fillable = [
        'invoice_number',
        'user',
        'due_date',
        'store',
        'customer',
        'invoice_total'

    ];
//    protected $casts = [
//        'created_at' => 'datetime:Y-m-d',
//    ];




    public function user_invoices():BelongsTo{
        return $this->belongsTo(User::class,'user','id');
    }
    public function invoice_with_stock(){
        return $this->belongsToMany(Stock::class,'invoice_products','invoice','stock',"id",'id')->withPivot('returned');
    }
    public function invoice_with_unreturned_stock(){
        return $this->belongsToMany(Stock::class,'invoice_products','invoice','stock',"id",'id')->withPivot('returned')->wherePivot('returned',0);
    }

    public function stock(){
        return $this->belongsToMany(Stock::class,'invoice_products','invoice','stock','id','id');
    }


    public function customer_invoice(){
        return $this->belongsToMany( Customer::class,'customer_invoices','invoice','customer','id','id');
    }

    public function payments(){
        return $this->hasMany(InvoicePayment::class,'invoice','id');
    }
    public function customer(){
        return $this->belongsTo(Customer::class,'customer','id');
    }
}
