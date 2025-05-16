<?php /** @noinspection DuplicatedCode */

namespace App\Http\Controllers\Sale;

use App\Http\Controllers\Controller;
use App\Http\Controllers\MyBaseController;
use App\Models\DailySummary;
use App\Models\Sale;
use App\Models\SaleDelivery;
use App\Models\SaleProduct;
use App\Models\Sales\NewSale;
use App\Models\Sales\NewSaleStock;


use App\Models\Stock;
use App\Models\Stock\NewStock;
use Carbon\Carbon;
use http\Env\Response;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use stdClass;

class NewSaleController extends MyBaseController
{


//    public function add_stock_name_to_sale_products()
//    {
//
//        try
//        {
//            DB::table('sale_products')->chunkById(1000, function (Collection $sale_products) {
//                foreach ($sale_products as $sale_product)
//                {
//                    $name_price = Stock::where('id', $sale_product->stock)->first(['stock_name', 'price', 'broker']);
//
//                    DB::table('sale_products')->where('id', $sale_product->id)->update([
//                        'stock_name' => Str::lower($name_price->stock_name),
//                        'price' => $name_price->price,
//                        'broker' => $name_price->broker
//                    ]);
//
//                }
//
//
//            });
//            return response()->json([
//
//                'status' => true,
//                'message' => 'name added successfully'
//            ]);
//
//        } catch (\Throwable $e)
//        {
//            return response()->json([
//                'status' => true,
//                'e-message' => $e->getMessage(),
//                'error' => $e->getTrace(),
//                'message' => 'an error occured'
//
//            ], 500);
//
//        }
//
//
//    }
//    public function add_new_sale_stocks($store = null, $month = null)
//    {
//        try
//        {
//            DB::table('sales')->where('store', $store)->whereMonth('date', $month)
//                ->chunkById(100,
//                    function (Collection $sales) {
//                    foreach ($sales as $sale)
//                    {
//                        $stocks = SaleProduct::where('sale', $sale->id)->get();
//                        foreach ($stocks as $stock)
//                        {
//                            $count = SaleProduct::where('stock_name', $stock->stock_name)->where('sale', $stock->sale)
//                                ->count();
//                            $new_stock_d = DB::table('new_stocks')->where('stock_name',$stock->stock_name)->first();
//                            $new_stock_id = $new_stock_d->id;
//                            $total = $count * ($stock->price + $stock->broker);
//
//                            DB::table('new_sale_stocks')->updateOrInsert([
//                                'sale' => $stock->sale,
//                                'stock' => $new_stock_id,
//                            ], [
//                                    'quantity' => $count,
//                                    'each' => $stock->price,
//                                    'broker' => $stock->broker,
//                                    'total' => $total,
//                                    'returned_total' => 0,
//                                    'returned' => 0,
//                                    'replaced' => 0
//                                ]
//                            );
//
//                        }
//
//
//                    }
//
//
//
//                });
//            return response()->json([
//                'status' => true,
//                'message' => 'Done Successfully',
//
//
//            ]);
//
//        } catch (\Throwable $e)
//        {
//            return response()->json([
//                'status' => false,
//                'message' => $e->getMessage(),
//                'line'=>$e->getLine(),
//                'error' => $e->getTrace()
//
//            ], 500);
//        }
//
//
//    }

    /**
     * Make new Sale
     * @param Request $request
     * @param null $store - store id number
     * @return JsonResponse
     *
     * */
    public function make_new_sale(Request $request, $store, $column): JsonResponse
    {

        $this->check_daily_summary($store);

        $validator = Validator::make($request->all(), [
            'stocks.*.selling_price' => 'required',
            'cash' => 'required_without:mpesa',
            'mpesa' => 'required_without:cash'

        ]);

        if ($validator->fails())
        {
            return response()->json([
                'status' => false,
                'message' => $validator->errors(),
                'errors' => $validator->errors()
            ], 500);
        }

        try
        {

            $stocks = $request->stocks;
            $cash = (integer)$request->cash;
            $total = (integer)$request->sale_total;
            $mpesa = (integer)$request->mpesa;
            $broker_total = (integer)$request->broker_total;


            DB::beginTransaction();
//            create_sale
            $sale = Sale::create([
                'user' => Auth::id(),
                'store' => $store,
                'mpesa' => (integer)$request->mpesa,
                'cash' => (integer)$request->cash,
                'receipt' => $this->receipt_number(),
                'deliverly_number' => $this->delivery_number(),
                "customer_name" => $request->customer_name,
                "customer_phone" => $request->customer_phone,
                'ref_number' => $request->ref_number,
                'sale_total' => $total,
                'bank' => $request->bank,
                'broker_total' => $broker_total,
                'date' => Carbon::parse(Carbon::now())->toDateString(),
                'time' => Carbon::parse(Carbon::now())->toTimeString(),
            ]);

            foreach ($stocks as $stock)
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

                $upgraded = false;

                if ($stock['upgraded_to'] && Str::lower($stock['upgraded_to']) != $stock['stock_name'])
                {
                    $upgraded = true;
                }

                NewSaleStock::create([
                    'sale' => $sale->id,
                    'stock' => $stock['id'],
                    'quantity' => $stock['sale_quantity'],
                    'each' => $stock['selling_price'],
                    'total' => $stock['sale_quantity'] * $stock['selling_price'],
                    'broker' => $stock['broker'],
                    'buying_price' => $stock['buying_price'],
                    'broker_total' => $stock['broker'] * $stock['sale_quantity'],
                    'returned_total' => 0,
                    'returned' => 0,
                    'serial_number' => $stock['serial'],
                    'replaced' => 0,
                    'date' => Carbon::parse(Carbon::now())->toDateString(),
                    'store' => $store,
                    'upgraded' => $upgraded,
                    'upgrade_to' => $upgraded ? Str::lower($stock['upgraded_to']) : null,
                ]);

                $quantity = $stock['sale_quantity'];
                $stock_ = $this->get_single_stock($column, $stock['id']);

                if ($stock_->stock_quantity < $quantity)
                {

                    DB::rollBack();
                    return response()->json([
                        'status' => false,
                        'message' => 'not enough stock to make sale'
                    ], 500);

                }

                $history->stock = $stock['id'];
                $history->action = 6;
                $history->buying_price = $stock['buying_price'];
                $history->selling_price = $stock['selling_price'];
                $history->quantity = $quantity;
                $history->store = $store;
                $history->previous_stock = $stock_->stock_quantity;
                $history->stock_after = $stock_->stock_quantity - $quantity;
                $history->serial_number = $stock['serial'];

                $this->make_stock_history($history);
                $this->deduct_stock($stock['id'], $column, $stock['sale_quantity']);
            }
            $return_sale = Sale::with('new_sale_stocks')->find($sale->id);

            DB::commit();
            return response()->json([
                'status' => true,
                'message' => 'sale successful',
                'sale' => $return_sale,

            ], 200);


        } catch (\Throwable $exception)
        {

            DB::rollBack();
            return response()->json([
                'status' => false,
                'message' => $exception->getMessage(),
                'error' => $exception->getTrace()

            ], 500);
        }


    }


    public function delete_store_sale($column, $sale_id)
    {

        try
        {
            $this->delete_sale($sale_id, $column);
            return response()->json([
                'status' => true,
                'message' => 'sale deleted successfully'
            ]);
        } catch (\Throwable $e)
        {
            return response()->json([
                'status' => false,
                'message' => $e->getMessage(),
                'error' => $e->getTrace()
            ], 500);
        }

    }


    /**
     * Edit new Sale
     * @param Request $request
     * @param null $store - store id number
     * @return JsonResponse
     *
     * */

    public function edit_new_sale(Request $request, $store, $column, $sale_id): JsonResponse
    {

        $this->check_daily_summary($store);

        $validator = Validator::make($request->all(), [
            'stocks.*.selling_price' => 'required',
            'cash' => 'required_without:mpesa',
            'mpesa' => 'required_without:cash'
        ]);

        if ($validator->fails())
        {
            return response()->json([
                'status' => false,
                'message' => $validator->errors(),
                'errors' => $validator->errors()
            ], 500);
        }
        try
        {
            $stocks = $request->stocks;


            DB::beginTransaction();
//            create_sale
            $sale = Sale::find($sale_id);
            $this->return_sale_stock($sale_id, $column);
            foreach ($stocks as $stock)
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

                $upgraded = false;

                if ($stock['upgraded_to'] && Str::lower($stock['upgraded_to']) != $stock['stock_name'])
                {
                    $upgraded = true;
                }

                DB::table('new_sale_stocks')
                    ->updateOrInsert([
                        'sale' => $sale->id,
                        'stock' => $stock['id'],
                        'store' => $store,
                    ],
                        [
                            'quantity' => $stock['sale_quantity'],
                            'each' => $stock['selling_price'],
                            'total' => $stock['sale_quantity'] * $stock['selling_price'],
                            'broker' => $stock['broker'],
                            'buying_price' => $stock['buying_price'],
                            'broker_total' => $stock['broker'] * $stock['sale_quantity'],
                            'returned_total' => 0,
                            'returned' => 0,
                            'serial_number' => $stock['serial_number'],
                            'replaced' => 0,
                            'date' => Carbon::parse(Carbon::now())->toDateString(),
                            'upgraded' => $upgraded,
                            'upgrade_to' => $upgraded ? Str::lower($stock['upgraded_to']) : null,
                        ]
                    );


                $quantity = $stock['sale_quantity'];
                $stock_ = $this->get_single_stock($column, $stock['id']);

                if ($stock_->stock_quantity < $quantity)
                {
                    DB::rollBack();
                    return response()->json([
                        'status' => false,
                        'message' => 'not enough stock to make sale'
                    ], 500);
                }


                $history->stock = $stock['id'];
                $history->action = 6;
                $history->buying_price = $stock['buying_price'];
                $history->selling_price = $stock['selling_price'];

                $history->quantity = $quantity;
                $history->store = $store;
                $history->previous_stock = $stock_->stock_quantity;
                $history->stock_after = $stock_->stock_quantity - $quantity;
//                $history->serial_number = $stock['serial'];


                $this->make_stock_history($history);

            }
            $sale->mpesa = (integer)$request->mpesa;
            $sale->cash = (integer)$request->cash;
            $sale->customer_name = $request->customer_name;
            $sale->ref_number = $request->ref_number;
            $sale->sale_total = (integer)$request->sale_total;
            $sale->broker_total = (integer)$request->broker_total;
            $sale->save();

            DB::commit();


            $return_sale = Sale::with('new_sale_stocks')->find($sale->id);
            return response()->json([
                'status' => true,
                'message' => 'sale successful',
                'sale' => $return_sale,

            ], 200);


        } catch (\Throwable $exception)
        {

            DB::rollBack();
            return response()->json([
                'status' => false,
                'message' => $exception->getMessage(),
                'error' => $exception->getTrace()

            ], 500);
        }


    }

    public function get_recent_sales_by_hrs($store = null, $hours = null)
    {
        try
        {
            $sales = $this->get_new_sales_today($store);
            return response()->json([
                'status' => true,
                'sales' => $sales
            ], 200);
        } catch (\Throwable $e)
        {
            return response()->json([
                'status' => false,
                'message' => $e->getMessage(),
                'error' => $e->getTrace()
            ], 500);
        }


    }


    public function get_month_sales(Request $request, $store, $year, $month)
    {
        try
        {


            $sales = $this->get_new_sales_month($year, $month, $store);
            $collect_sales = collect($sales);
            $grouped_sales = $collect_sales->groupBy('date');

            return response()->json([
                'status' => true,
                'data' => $grouped_sales
            ], 200);

        } catch (\Throwable $e)
        {
            return response()->json([
                'status' => false,
                'message' => $e->getMessage()
            ], 500);

        }

    }
}


