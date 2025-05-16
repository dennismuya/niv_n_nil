<?php

namespace App\Http\Controllers;


use App\Models\Customer;
use App\Models\Product;
use App\Models\Stock;

use App\Models\StoreUser;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

use stdClass;

class StockController extends Controller
{
    /**
     * Add stock
     *
     */


    public function add_stock(Request $request)
    {
        $validateUser = Validator::make($request->stock,
            [

                '*.product' => 'required',
//                '*.price' => 'required',

            ]);

        if ($validateUser->fails()) {
            return response()->json([
                'status' => false,
                'message' => 'validation error',
                'errors' => $validateUser->errors()
            ], 401);
        }
        $Stock = $request->stock;
        $added_stock = [];

        $supplier = Customer::where('id', $request->supplier)->first();


        foreach ($Stock as $stock_) {
            try {

                $product_name = Product::where('id', $stock_['product'])->get()->first();
                $stock__ = Stock::create([
                    'properties' => $stock_['properties'],
                    'store' => $stock_['store'],
                    'product' => $stock_['product'],
                    'price' => $stock_['price'],
                    'serial' => $stock_['serial'],
                    'ready' => true,
                    'sold' => 0,
                    'stock_name' => $product_name->name . " " . $stock_['properties'],
                    'buying_price' => $stock_['buying_price'],
                    'user' => Auth::id()
                ]);
                if ($request->supplier) {
                    $supplier->supplies()->attach($stock__->id);
                }


                $count_ = Stock::where('stock_name', $stock__->stock_name)->where('sold', false)->where('store', $stock_['store'])->count();
                Stock::where('stock_name', $stock__->stock_name)->where('sold', false)->where('store', $stock_['store'])->update([
                    'stock_quantity' => $count_
                ]);


                array_push($added_stock, $stock__);

            } catch (\Throwable $e) {
                return response()->json([
                    "status" => false,
                    "message" => "stock add failed",
                    "data" => $e->getMessage()

                ]);


            }
        }


        return response()->json([
            "status" => true,
            "message" => "stock added successfully",
            "data" => $added_stock

        ]);


    }

    /**
     * Edit Stock
     *
     */

    /**
     * Delete Stock
     */

    /**
     * Get All Stock
     */


    public function update_stock_chini_ya_mnazi()
    {

        DB::table('stocks')->where('store',1)->orderBy('id')->chunkById(200, function (Collection $stocks,) {
            foreach ($stocks as $stock) {
                $count = DB::table('stocks')->where('stock_name', $stock->stock_name)->where('store', $stock->store)->where('sold',false)->count();
                DB::table('stocks')->where('stock_name', $stock->stock_name)->where('store', $stock->store)->update([
                    'stock_quantity' => $count
                ]);


            }
            return false;
        });


//        $stocks = Stock::where('store',$store)->get();
//
//        foreach ($stocks as $stock) {
//            $stock_ = Stock::where('store',$store)->where('sold',0)->where('stock_name', $stock->stock_name)->get();
//
//            $stock->stock_quantity = count($stock_);
//
//            $stock->save();
//        }


    }
    public function update_stock_nation()
    {

        DB::table('stocks')->where('store',2)->orderBy('id')->chunkById(200, function (Collection $stocks,) {
            foreach ($stocks as $stock) {
                $count = DB::table('stocks')->where('stock_name', $stock->stock_name)->where('sold',false)->where('store', $stock->store)->count();
                DB::table('stocks')->where('stock_name', $stock->stock_name)->where('store', $stock->store)->update([
                    'stock_quantity' => $count
                ]);


            }
            return false;
        });


//        $stocks = Stock::where('store',$store)->get();
//
//        foreach ($stocks as $stock) {
//            $stock_ = Stock::where('store',$store)->where('sold',0)->where('stock_name', $stock->stock_name)->get();
//
//            $stock->stock_quantity = count($stock_);
//
//            $stock->save();
//        }


    }
    public function update_stock_nakuru()
    {

        DB::table('stocks')->where('store',3)->orderBy('id')->chunkById(300, function (Collection $stocks,) {
            foreach ($stocks as $stock) {
                $count = DB::table('stocks')->where('stock_name', $stock->stock_name)->where('sold',false)->where('store', $stock->store)->count();
                DB::table('stocks')->where('stock_name', $stock->stock_name)->where('store', $stock->store)->update([
                    'stock_quantity' => $count
                ]);


            }
            return false;
        });


//        $stocks = Stock::where('store',$store)->get();
//
//        foreach ($stocks as $stock) {
//            $stock_ = Stock::where('store',$store)->where('sold',0)->where('stock_name', $stock->stock_name)->get();
//
//            $stock->stock_quantity = count($stock_);
//
//            $stock->save();
//        }


    }

    public function update_stock($store){
        if($store ==1){
            $this->update_stock_chini_ya_mnazi();
        }elseif ($store == 2){
            $this->update_stock_nation();
        }else{
            $this->update_stock_nakuru();
        }
    }

    public function get_stock($store = null)
    {
        $store_id = $store;
        $state = false;

        $stocks = Stock::with('product')->where('deleted', $state)->where('store', $store_id)->where('ready', true)->get();


        return response()->json([
            'data' => $stocks

        ]);
    }


    public function transfer_stock(Request $request, $store = null)
    {
        $stock = $request->stock;
        $to_store = $request->to_store;
        $user = Auth::id();

        try {

            foreach ($stock as $stock_) {
                $find_stocks = Stock::where('product', $stock_['product']['id'])->where('deleted', 0)->where('sold', 0)->where('store', $store)->limit($stock_['transfer_quantity'])->get();

                if (!count($find_stocks)) {
                    return response()->json([
                        'status' => false,
                        'message' => 'stock transfer failed'
                    ], 500);
                }

                foreach ($find_stocks as $find_stock) {
                    $find_stock->store = $to_store;
                    $find_stock->properties = $stock_['properties'];
                    $find_stock->serial = $stock_['serial'];
                    $find_stock->price = $stock_['price'];
                    $find_stock->transferred_date = Carbon::now();
                    $find_stock->transferred = true;
                    $find_stock->ready = false;
                    $find_stock->transferred_by = $user;
                    $find_stock->save();

                    $count_ = Stock::where('stock_name', $stock_['stock_name'])->where('sold', false)->where('store', $to_store)->count();
                    Stock::where('stock_name', $stock_['stock_name'])->where('sold', false)->where('store', $to_store)->update([
                        'stock_quantity' => $count_
                    ]);


                }


            }
            return response()->json([
                'status' => true,
                'message' => " stock transferred successful"
            ]);


        } catch (\Throwable $e) {
            return response()->json([
                'status' => false,
                'message' => $e->getMessage()

            ], 500);
        }


//        $store = Store::find($to_store)->toArray();
//        $stock = $request->stock;
////        $user = Auth::user();
////         stockMoved::dispatch($user,$store,$stock);
//
////        StockShare::dispatch($store);


    }

    public function get_transferred_stock(Request $request, $store = null)
    {
        try {
            $stocks = Stock::orderByDesc('transferred_date')->where('store', $store)->where('transferred', true)->where('received', false)->where('deleted', 0)->with('product')->get();


            return response()->json([
                'status' => true,
                'transferred_stock' => $stocks

            ]);


        } catch (\Throwable $e) {
            return response()->json([
                'status' => false,
                'message' => 'an error occurred ' . $e->getMessage()
            ]);

        }


    }

    public function receive_all_transferred(Request $request, $store = null)
    {
        try {

            $stocks = Stock::where('store', $store)->where('transferred', true)->where('deleted', 0)->where('received', false)->get();

            if (!$stocks) {
                return response()->json([
                    'status' => false,
                    'message' => 'stock receiving failed'

                ], 500);
            }

            foreach ($stocks as $stock) {

                Stock::where('stock_name', $stock->stock_name)->where('sold', false)->where('store', $stock->store)->update([
                    'received' => true,
                    'ready' => true,
                    'received_by' => Auth::id()
                ]);




            }

            $this->update_stock($store);



            return response()->json([
                'status' => true,
                'message' => 'stock received successfully'

            ]);


        } catch (\Throwable $e) {
            return response()->json([
                'status' => false,
                'message' => 'an error occurred ' . $e->getMessage()
            ]);

        }


    }


    public function edit_stock(Request $request)
    {

        try {
            $product = $request->product;
            $props = $request->props;
            $change_to = $request->change_to;

            $product_ = Product::where('id', $product)->get()->first();

            $stock = DB::table('stocks')->where('product', $product)->where('properties', $props)->update(['properties' => $change_to, 'stock_name' => $product_->name . " " . $change_to]);


            return response()->json([
                'status' => true,
                'message' => $stock
            ]);
        } catch (\Throwable $e) {

            return response()->json([
                'status' => false,
                'message' => $e->getMessage()

            ]);

        }

    }

    public function soft_delete_one($stock = null)
    {
        try {
            $del = Stock::find($stock)->first();


            if (!$del) {
                return response()->json([
                    'status' => false,
                    'message' => 'delete stock failed',

                ], 500);
            }

            $del->delete();

            return response()->json([
                'status' => true,
                'message' => 'stock deleted successfully'
            ]);

        } catch (\Throwable $e) {

            return response()->json([
                'status' => false,
                'message' => 'delete stock failed',
                'error' => $e->getMessage()
            ]);


        }

    }


    public function transferred_stock($store = null)
    {
        $stock = new stdClass();

        $received_stocks = Stock::where('transferred', true)->latest('transferred_date')->where('store', $store)->get();

        foreach ($received_stocks as $received_stock) {

            $store__ = User::where('id', $received_stock->transferred_by)->get()->first();
            $name = $store__->shop()->first();
            $received_stock->received_from = $name->name;
            $new_date = Carbon::parse($received_stock->transferred_date);
            $received_stock->transfer_date = $new_date->toDateString();

        }

        $collect_received = collect($received_stocks);
        $sorted_received = $collect_received->sortBy('transfer_date');;
        $grouped_received = $sorted_received->groupBy('transfer_date');


        $transferred_by_user = [];


        $store_users = StoreUser::where('shop', $store)->get('user')->pluck('user');

        foreach ($store_users as $store_user) {

            $stocks = Stock::where('transferred_by', $store_user)->latest('transferred_date')->with(['store' => function ($query) {
                $query->select('id', 'name');

            }])->get()->toArray();

            foreach ($stocks as $stock___) {
                $new_date = Carbon::parse($stock___['transferred_date']);
                $stock___['transferred_to'] = $stock___['store']['name'];
                $stock___['transfer__date'] = $new_date->toDateString();
                array_push($transferred_by_user, $stock___);
            }

        }


        array_reverse($transferred_by_user);

        $collect_transferred = collect($transferred_by_user);
        $sorted_transferred = $collect_transferred->sortBy('transfer_date');
        $grouped_transferred = $sorted_transferred->groupBy('transfer__date');


        $stock->received_stock = $grouped_received;
        $stock->transferred_stock = $grouped_transferred;


        return response()->json([
            'status' => true,
            'stock' => $stock
        ]);


    }


//    delete stock

    public function delete__stock(Request $request, $store = null)
    {

        try {
            $stock_to_delete = $request->stock;
            $to_delete_quantity = $request->quantity;

            $stocks = Stock::doesnthave('supplier')->doesnthave('invoice')->where('store', $store)->where('product', $stock_to_delete['product']['id'])->where('transferred', 0)->where('sold', 0)->where('received', 0)->limit($to_delete_quantity)->get();


            if (!$stocks || !count($stocks)) {
                return response()->json([
                    'status' => false,
                    'message' => 'there isnt that much stock'

                ], 500);
            }
            foreach ($stocks as $stock) {

                $stock->delete();

            }

            return response()->json([
                'status' => true,
                'message' => 'stock deleted successifully'

            ], 200);
        } catch (\Throwable $e) {
            return response()->json([
                'status' => false,
                'message' => $e->getMessage()

            ], 500);
        }
    }


    public function add_stock_name($store = null)
    {

        try {
            $stocks = Stock::where('store', $store)->get();

            foreach ($stocks as $stock) {
                $product_ = Product::where('id', $stock->product)->first();

                $stock->stock_name = $product_->name . " " . $stock->properties;


                $stock->save();
            }

            return response()->json([
                'status' => true,
                'message' => 'name created successfully'

            ]);

        } catch (\Throwable $e) {
            return response()->json([
                'status' => false,
                'error' => $e->getMessage(),
                'stack' => $e->getTrace(),


            ], 500);

        }


    }


    public function add_stock_quantity($store = null, $letter = null)
    {


        try {
            $stocks = Stock::where('store', $store)->get();

            foreach ($stocks as $stock) {
                $stock_ = Stock::where('store', $store)->where('sold', 0)->where('stock_name', 'LIKE', "{$letter}")->where('deleted', false)->where('ready', true)->where('stock_name', $stock->stock_name)->get();

                $stock->stock_quantity = count($stock_);

                if (!$stock->transferred || $stock->received) {
                    $stock->ready = true;
                }

                $stock->save();
            }


            return response()->json([
                'status' => true,
                'message' => 'Quantity created successfully'

            ]);

        } catch (\Throwable $e) {
            return response()->json([
                'status' => false,
                'error' => $e->getMessage(),
                'stack' => $e->getTrace(),


            ], 500);

        }

    }



    /**
     * Search
     */

    /**
     * Get by Category
     */
}
