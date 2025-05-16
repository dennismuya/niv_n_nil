<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use App\Models\Product;
use App\Models\Stock;
use Carbon\Carbon;
use Illuminate\Http\Request;

class CustomerSupplyController extends Controller
{
    //

    public function supplier_with_stock($store = null)
    {
        try {
            $suppliers = Customer::has('supplies')->where('store', $store)->with('supplies')->get();
            foreach ($suppliers as $supplier){
                foreach ($supplier->supplies as $supply){
                    $product_name = Product::where('id',$supply->product)->get('name')->first();
                    $supply->product_name = $product_name->name;
                    $supply->name =$product_name->name. " ".$supply->properties;
                }
            }
            return response()->json([
                'status' => true,
                'data' => $suppliers
            ]);
        } catch (\Throwable $e) {
            return response()->json([
                'status' => false,
                'error' => $e->getMessage()
            ]);

        }


    }

    public function suppliers($store=null)
    {
        try {
            $suppliers = Customer::has('supplies')->where('store', $store)->get();

            foreach ($suppliers as $supplier){
                foreach ($supplier->supplies as $supply){
                    $product_name = Product::where('id',$supply->product)->get('name');
                    $supply->product_name = 'e';
                }
            }
            return response()->json([
                'status' => true,
                'suppliers' => $suppliers

            ]);

        } catch (\Throwable $e) {
            return response()->json([
                'status' => false,
                'error' => $e->getMessage()

            ]);

        }


    }

    public function stores_suppliers()
    {
        try {
            $suppliers = Customer::has('supplies')->get()->toArray();

            $collection = collect($suppliers);
            $grouped_suppliers = $collection->groupBy('store');

            return response()->json([
                'status' => true,
                'suppliers' => $grouped_suppliers

            ]);


        } catch (\Throwable $e) {
            return response()->json([
                'status' => false,
                'error' => $e->getMessage()

            ]);

        }


    }

    public function supplier_stock($supplier = null)
    {
        try {
            $supplier = Customer::where('id', $supplier)->first();
            $stocks = $supplier->supplies()->get();


            foreach ($stocks as $stock){
                $new_date = Carbon::parse($stock->created_at);
                $stock->date = $new_date->toDateString();
                $product_name = Product::where('id',$stock->product)->get('name')->first();
                $stock->product_name = $product_name->name;
                $stock->name =$product_name->name. " ".$stock->properties;

            }

            $stock_collect = collect($stocks);
            $stock_group = $stock_collect->groupBy('date');
            return response()->json([
                'status' => true,
                'supplies' => $stock_group,
            ]);

        } catch (\Throwable $e) {
            return response()->json([
                'status' => false,
                'error' => $e->getMessage()
            ], 500);

        }

    }


    public function update_buying_price(Request $request){


        try {

            $stocks = $request->stock;


            foreach ($stocks as $stock){

                Stock::where('id',$stock)->update([
                    'buying_price'=>$request->new_buying_price
                ]);


            }

            return response()->json([
                'status'=>true,
                'message'=>'buying price update successfull'

            ],500);

        }catch (\Throwable $e){

            return response()->json([
                'status'=>false,
                'message'=>$e->getMessage()

            ],500);

        }








    }




}

