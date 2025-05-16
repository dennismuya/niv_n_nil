<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Store extends Model
{
    use HasFactory;
    protected $fillable=[
        'name',
        'store_name',
        'location',
        'building',
        'primary_phone',
        'secondary_phone',
        'website',
        'tagline',
        'products'
    ];

    public function store_users(){
        return $this->belongsToMany(User::class,'store_users','shop','user','id','id');
    }



}


