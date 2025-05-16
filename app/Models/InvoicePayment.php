<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class InvoicePayment extends Model
{
    use HasFactory;
    protected $fillable = [
        'customer',
        'cash',
        'mpesa',
        'total_payment',
        'mpesa_ref',
        'bank',
        'cheque_amount',
        'cheque_number',
        'cheque_date',
        'date',
        'store',
        'comment'
    ];
}
