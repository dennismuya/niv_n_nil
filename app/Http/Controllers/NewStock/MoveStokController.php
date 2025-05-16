<?php

namespace App\Http\Controllers\NewStock;

use App\Http\Controllers\Controller;
use App\Models\MainStock;
use App\Models\Sale;
use App\Models\Stock;
use Illuminate\Http\Request;

class MoveStokController extends Controller
{
    //

    public function move_stock(){

        try {
            $stock = Stock::where('store',1)->get();

            foreach ($stock as $item){
                $new_stock =  MainStock::create([
                    'id'=>$item->id,
                    'store'=>$item->store,
                    'product'=>$item->product,
                    'properties'=>$item->properties,
                    'price'=>$item->price,
                    'deleted'=>$item->deleted,
                    'deleted_by'=>$item->deleted_by,
                    'received'=>$item->received,
                    'transferred_date'=>$item->transferred_date,
                    'returned'=>$item->returned,
                    'returned_date'=>$item->returned_date,
                    'transferred'=>$item->transferred,
                    'received_by'=>$item->received_by,
                    'transferred_by'=>$item->transferred_by,
                    'buying_price'=>$item->buying_price,
                    'serial'=>$item->serial,
                    'sold'=>$item->sold,
                    'broker'=>$item->broker,

                ]);



            }

            return response()->json([
                'status'=>true,
                'message'=>'move successfull'

            ],200);

        }catch (\Throwable $e){
            return response()->json([
                'status'=>false,
                'message'=>'move failed'

            ],500);
        }


    }



//    check sale items

public function sale_items($store=null){
        try
    {
        $stocks = [];
        $sales = Sale::where('store', $store)->with('sale_stock')->get();

        foreach ($sales as $sale) {
            foreach ($sale->sale_stock as $sale__stock) {

                array_push($stocks, $sale__stock);
            }


        }

        return response()->json([
            'status' => true,
            'data' => $stocks

        ]);
    }catch (\Throwable $e){

            return response()->json([
                'status' => true,
                'error' => $e->getMessage()

            ]);
        }



}






}
