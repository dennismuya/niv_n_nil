<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Expense extends Model
{
    use HasFactory;
    protected $fillable=[
        'user',
        'created_by',
        'amount',
        'reason',
        'store',
        'date',
        'time'
    ];
    public function user():BelongsTo{
        return $this->belongsTo(User::class, 'user', 'id');
    }
}
