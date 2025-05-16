<?php

namespace App\Models;


use App\Models\Debt\DebtHistory;
use App\Models\Stock\StockHistory;
use App\Models\Stock\SupplierHistory;
use DateTimeInterface;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Support\Carbon;

class Customer extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'store',
        'phone1',
        'phone2',
        'phone3',
        'phone_number'
    ];

    protected function serializeDate(DateTimeInterface $date): string
    {
        return $date->format('Y-m-d h:m:s');
    }

    public function customer_invoices(): BelongsToMany
    {
        return $this->belongsToMany(Invoice::class, 'customer_invoices', 'customer', 'invoice', 'id', 'id');
    }

//    public function supplies(): BelongsToMany
//    {
//        return $this->belongsToMany(Stock::class, 'customer_supplies', 'customer', 'stock', 'id', 'id');
//    }

    public function supplies(): BelongsToMany
    {
        return $this->belongsToMany(\App\Models\Stock\NewStock::class,
            'supplier_histories', 'supplier',
            'stock', 'id', 'id')
            ->withPivot(['buying_price', 'supply_date', 'quantity', 'serial_number','total_price']);

//        return $this->hasMany(SupplierHistory::class,'supplier','id');
    }

//    public function supplies_total(): \Illuminate\Database\Eloquent\Relations\HasMany
//    {
//        return $this->hasMany(StockHistory::class, 'supplier', 'id')->withSum('stock_histories', 'buying_price');
////            ->withSum('stock_histories','buying_price');
//    }


    public function new_invoices()
    {
        return $this->hasMany(NewInvoice::class, 'customer', 'id');
    }

    public function payments()
    {
        return $this->hasMany(InvoicePayment::class, 'customer', 'id');
    }

    public function debt_history()
    {
        return $this->hasMany(DebtHistory::class, 'customer', 'id');
    }

    public function debt()
    {
        return $this->hasMany(DebtHistory::class, 'customer', 'id');
    }


    public function invoices()
    {
        return $this->hasMany(Invoice::class, 'customer', 'id');
    }


}
