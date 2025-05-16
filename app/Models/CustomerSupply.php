<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CustomerSupply extends Model
{
    use HasFactory;
    protected $fillable =[
        'stock',
        'customer'
    ];


}
