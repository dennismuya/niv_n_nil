<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Deposit extends Model
{
    use HasFactory;
    protected $fillable = [
        'user',
        'amount',
        'bank',
        'store',
        'date'
    ];


    public function bank(){
        return $this->belongsTo('banks','bank','id',);
    }
}
