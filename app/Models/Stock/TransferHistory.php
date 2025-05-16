<?php

namespace App\Models\Stock;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TransferHistory extends Model
{
    use HasFactory;

    protected $fillable = [
        'stock',
        'origin_store',
        'destination_store',
        'transfer_date',
        'transferred_quantity',
        'received_quantity',
        'pedding_quantity',
        'transferred_by',
        'received_by',
        'transfer_serial_array',
        'transfer_serials',

    ];

    public function stock(): BelongsTo
    {
        return $this->belongsTo(NewStock::class, 'stock', 'id');
    }
}
