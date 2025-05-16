<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DailySummary extends Model
{
    use HasFactory;

    protected $fillable = [
        'date',
        'opening_time',
        'store',
        'opening_balance',
        'sales_cash',
        'sales_mpesa',
        'sales_total',
        'sales_debt',
        'expenses',
        'supply_payments',
        'closing_balance',
        'closing_time',
        'debt_recovered_cash',
        'debt_recovered_mpesa',
        'debt_recovered_total',
        'bank_deposits'
    ];
    public function store_(){
        return $this->belongsTo(Store::class,'store','id');
    }
}
