<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class InvoiceDelivery extends Model
{
    use HasFactory;
    protected $fillable =[
        'invoice',
        'delivery_number'
    ];


}
