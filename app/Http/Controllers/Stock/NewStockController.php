<?php

namespace App\Http\Controllers\Stock;


use App\Http\Controllers\MyBaseController;
use App\Models\Stock;
use App\Models\Stock\NewStock;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use stdClass;
use function Ramsey\Collection\add;

class NewStockController extends MyBaseController
{
    //


    public function create_new_stock(Request $request, $column)
    {
        $validateStock = Validator::make($request->all(),
            [
                'stock_name' => 'required|unique:new_stocks',

            ]);
        if ($validateStock->fails())
        {
            return response()->json([
                'status' => false,
                'message' => 'stock name provided already exists',
                'errors' => $validateStock->errors()
            ], 500);
        }

        try
        {
            $stock = new stdClass();
            $stock->stock_name = $request->stock_name;
            $stock->properties = $request->properties;
            $stock->quantity = $request->quantity;
            $stock->price = $request->price;

            $stock = $this->add__new__stock($column, $stock);

            return response()->json([
                'status' => true,
                'message' => 'stock added successfully',
                'stock' => $this->get_single_stock($column, $stock->id)
            ]);
        } catch (\Throwable $e)
        {
            return response()->json([
                'status' => false,
                'message' => $e->getMessage(),
                'error' => $e->getMessage()
            ], 500);
        }

    }


//    public function transfer_to_new($lower_limit = null, $upper_limmit = null)
//    {
//        try
//        {
//            DB::table('stocks')->where('id', '>=', $lower_limit)->where('id', '<=', $upper_limmit)->lazyById(1000)
//                ->each(function (object $stock) {
//                    $count_mnazi = DB::table('stocks')->where('store', 1)->where('stock_name', $stock->stock_name)
//                        ->where('sold', false)->count();
//                    $count_nakuru = DB::table('stocks')->where('store', 3)->where('stock_name', $stock->stock_name)
//                        ->where('sold', false)->count();
//                    $count_nation = DB::table('stocks')->where('store', 2)->where('stock_name', $stock->stock_name)
//                        ->where('sold', false)->count();
//                    DB::table('new_stocks')->updateOrInsert([
//                        'stock_name' => Str::lower($stock->stock_name),
//                        'product_id' => $stock->product,
//                    ], [
//                            'nakuru_quantity' => $count_nakuru,
//                            'stock_properties' => Str::lower($stock->properties),
//                            'old_nation_quantity' => $count_nation,
//                            'chini_ya_mnazi_quantity' => $count_mnazi,
//                            'selling_price' => $stock->price ?: 0,
//                        ]
//                    );
//
//                });
//
//            return response()->json([
//                'status' => true,
//                'message' => 'stock transferred successfully',
//
//
//            ]);
//
//
//        } catch (\Throwable $e)
//        {
//            return response()->json([
//                'status' => false,
//                'message' => $e->getMessage(),
//                'trace' => $e->getTrace()
//
//            ], 500);
//
//
//        }
//
//    }


    public function add_new_stock(Request $request, $store = null, $column = null, $stock_id = null)
    {

        try
        {
            $serial_number = $request->serial_number;
            $supplier = $request->supplier;
            $buying_price = $request->buying_price;
            $supply_date_ = $request->supply_date ?: Carbon::parse(Carbon::now())
                ->toDateString();

            if (!$supplier)
            {
                return response()->json([
                    'status' => false,
                    'message' => 'you cannot add stock without supplier'

                ], 500);
            }
            if (!$buying_price)
            {
                return response()->json([
                    'status' => false,
                    'message' => 'you cannot add stock without buying price'

                ], 500);
            }
            $stock__ = $this->add_stock($store, $stock_id, $column, $request->quantity, $supplier, $buying_price,
                $serial_number);
            $this->add_supply($stock_id,$request->quantity,$supplier,$buying_price,$supply_date_,$serial_number);

            return response()->json([
                'status' => true,
                'message' => $request->quantity . " " . $stock__->stock_name . ' added successfully',
                'stock' => $this->get_stock($column),
                'update_d' => $stock__
            ]);


        } catch (\Throwable $exception)
        {
            return response()->json([
                'status' => false,
                'message' => $exception->getMessage(),
                'error' => $exception->getTrace()
            ], 500);
        }


    }

    public function update_new_stock(Request $request, $store = null, $column = null, $stock_id = null)
    {
        $history = new stdClass();
        $history->stock = null;
        $history->stock_action = null;
        $history->buying_price = null;
        $history->selling_price = null;
        $history->quantity = null;
        $history->supplier = null;
        $history->user = null;
        $history->store = null;
        $history->previous_stock = null;
        $history->stock_after = null;
        $history->reason = null;
        $history->serial_number = null;
        $history->serial_array = null;
        $history->returned_from = null;
        $history->replaced = null;
        $history->sale_replaced = null;
        try
        {
            $stock_ = $this->get_single_stock($column, $stock_id);

            $stock__ = $this->update_stock($stock_id, $column, $request->quantity);

            $history->stock = $stock_id;
            $history->action = 8;
            $history->quantity = $request->quantity;
            $history->store = $store;
            $history->previous_stock = $stock_->stock_quantity;
            $history->stock_after = $request->quantity;

            $this->make_stock_history($history);

            return response()->json([
                'status' => true,
                'message' => $request->quantity . " " . $stock_->stock_name . ' updated successfully',
                'stock' => $this->get_stock($column),
                'update_d' => $stock__
            ]);


        } catch (\Throwable $exception)
        {
            return response()->json([
                'status' => false,
                'message' => $exception->getMessage(),
                'error' => $exception->getTrace()
            ], 500);
        }


    }


    public function update_stock_name(Request $request,$stock,$column){
        $validateStock = Validator::make($request->all(),
            [
                'stock_name' => 'required|unique:new_stocks',

            ]);
        if ($validateStock->fails())
        {
            return response()->json([
                'status' => false,
                'message' => 'stock name provided already exists',
                'errors' => $validateStock->errors()
            ], 500);
        }

        try {

            $stock_ =  NewStock::find($stock);
            $stock_->stock_name = $request->stock_name;
            $stock_->save();


            $stocks = $this->get_stock($column);
            return response()->json([
                'status' => true,
                'message' => 'stock updated successfully',
                'stock'=> $stocks
            ], 200);

        }catch (\Throwable $e){
            return response()->json([
                'status' => false,
                'message' => 'an error occured',
                'errors' => $e->getMessage()
            ], 500);

        }


    }

    public function delete_new_stock(Request $request, $store = null, $stock_id = null)
    {

        try
        {
            $quantity = $request->quantity;
            if ($store === 1)
            {

                DB::table('new_stocks')->decrement('chini_ya_mnazi_quantity', $quantity, ['id' => $stock_id]);

            } else if ($store === 2)
            {
                DB::table('new_stocks')->decrement('old_nation_quantity', $quantity, ['id' => $stock_id]);

            } else if ($store === 3)
            {
                DB::table('new_stocks')->decrement('nakuru_quantity', $quantity, ['id' => $stock_id]);

            } else
            {

                return response()->json([
                    'status' => false,
                    'message' => "invalid store given"
                ], 500);
            }

            return response()->json([
                'status' => true,
                'message' => 'stock deleted successfully'
            ]);

        } catch (\Throwable $exception)
        {
            return response()->json([
                'status' => false,
                'message' => $exception->getMessage(),
                'error' => $exception->getTrace()
            ], 500);
        }


    }

    public function get_new_stock($column = null)
    {

        try
        {

            $stock = $this->get_stock($column);
            return response()->json([
                'status' => true,
                'message' => 'stock retrieved successfully',
                'stock' => $stock

            ]);


        } catch (\Throwable $e)
        {
            return response()->json([
                'status' => false,
                'message' => $e->getMessage(),
                'error' => $e->getTrace()

            ], 500);
        };


    }

    public function soft_delete(Request $request, $column = null, $stock_id = null)
    {
        $this->delete_stock($stock_id);
        return response()->json([
            'status' => true,
            'message' => 'stock deleted successfully',
            'stock' => $this->get_stock($column)

        ]);
    }

}
