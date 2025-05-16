<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class StockResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id'=> $this->id,
            "properties"=> $this->properties,
            "store"=> $this->store,
            "product_id"=> $this->product['id'],
            "barcode"=>$this->product['SKU'],
            "broker"=> $this->broker,
            "product"=>$this->product['name'],
            'stock_name'=> $this->stock_name,
            "price"=> $this->price,
//            "available_stock"=>$this->available_stock,
            "serial"=> $this->serial,
            "transferred"=>$this->transferred,
            "sold"=> $this->sold,
//            'all'=>$this->all_stock,
//            'sold_'=>$this->sold_stock

        ];
    }
}
