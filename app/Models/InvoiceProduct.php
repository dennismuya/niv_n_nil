<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class InvoiceProduct extends Model
{
    use HasFactory;
    protected $fillable =[
        'invoice',
        'stock',
        'returned',
        'returned_by',
        'returned_date',
        'picked_at',

    ];

    public function invoice():BelongsTo{
        return $this->belongsTo(Invoice::class,'invoice','id');
    }
    public function stock(){
        return $this->belongsTo(Stock::class,'stock','id');
    }
}
