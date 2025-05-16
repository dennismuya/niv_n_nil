<?php

namespace App\Http\Controllers;

use App\Http\Resources\StockResource;
use App\Models\Product;
use App\Models\Sale;
use App\Models\SaleDelivery;
use App\Models\SaleProduct;
use App\Models\Stock;
use Carbon\Carbon;
use Illuminate\Database\Query\JoinClause;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use function Monolog\toArray;
use function NunoMaduro\Collision\Exceptions\getLine;

class SaleController extends Controller
{


    /**
     * Generate Receipt Number
     * @returns integer
     * */
    public function receipt_number()
    {
        $letters = range('A', 'Z');
        $day = Carbon::now()->dayOfWeek;
        $month = Carbon::now()->month;
        $now = Carbon::now();
        $hour = Carbon::now()->minute;
        $end_of_year = Carbon::now()->endOfYear();
        $diff_days = $now->diffInDays($end_of_year, false);


        $record_id = Sale::latest()->get('id')->first();
        $num = 1;
        $rad = mt_rand(300,2205);
        $rad_ = mt_rand(13,14132);



        $receipt_num = '#' . $letters[$day] . $letters[$month] . $rad_ . $rad;

        return $receipt_num;

    }

    public function delivery_number()
    {
        $letters = range('A', 'Z');
        $day = Carbon::now()->dayOfWeek;
        $now = Carbon::now();
        $hour = Carbon::now()->hour;
        $end_of_year = Carbon::now()->endOfYear();
        $diff_days = $now->diffInDays($end_of_year, false);


        $deliv = 0;

        $last_delivery = SaleDelivery::latest()->get('id')->first();


        if ($last_delivery) {
            $deliv = $last_delivery->id;
        }

        $delivery_number = '#' . 'd' . $diff_days . $letters[$hour] . $deliv + 1;

        return $delivery_number;


    }


    public function update_stock_quantity($store = null, $stock_name = null)
    {

        $count_ = Stock::where('stock_name', $stock_name)->where('sold', false)->where('store', $store)->count();
        Stock::where('stock_name', $stock_name)->where('sold', false)->where('store', $store)->update([
            'stock_quantity' => $count_,

        ]);
    }

    public function make_sale(Request $request, $store = null)
    {
        $validator = Validator::make($request->all(), [
            'products.*.price' => 'required',
//            'products.*.properties' => 'required',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'message' => 'You cannot make a sale without price',
                'errors' => $validator->errors()
            ], 401);
        }

        $sum_broker = 0;
        $sale_total = 0;

        $done_products = [];
        $sale_products = $request->products;


        foreach ($sale_products as $product) {
            $count = Stock::where('store', $store)->where('product', $product['product']['id'])->where('sold', false)->count();

            if (!$count || $count < $product['quantity']) {
                return response()->json([
                    'status' => false,
                    'message' => $product['stock_name'] . ' is of stock! only ' . $count . " remaining",
                ], 500);
            }

            $sale_total += $product["price"] * $product['quantity'];
            if (isset($product["broker"])) {
                $sum_broker += $product["broker"] * $product['quantity'];
            }
        }
        try {

            $sale = Sale::create([
                'user' => Auth::id(),
                'store' => $store,
                'mpesa' => (integer)$request->mpesa,
                'cash' => (integer)$request->cash,
                'receipt' => $this->receipt_number(),
                "customer_name" => $request->customer_name,
                "customer_phone" => $request->customer_phone,
                'ref_number' => $request->ref_number,
                'sale_total' => $sale_total,
                'bank' => $request->bank,
                'broker_total' => $sum_broker,
                'date' => Carbon::parse(Carbon::now())->toDateString(),
                'time' => Carbon::parse(Carbon::now())->toTimeString(),
            ]);

            if ($sale) {
                $deliver_ = SaleDelivery::create([
                    'sale' => $sale->id,
                    'delivery_number' => $this->delivery_number()
                ]);
                $delivery = $deliver_;

                foreach ($sale_products as $sale_product) {
                    $sold_items = Stock::where('store', $store)->where('product', $sale_product['product']['id'])->where('sold', false)->limit($sale_product['quantity'])->get();

                    foreach ($sold_items as $sold_item) {


                        $sell_product = SaleProduct::create([
                            'sale' => $sale->id,
                            'stock' => $sold_item['id'],
                        ]);

                        $sold_Product = Stock::where('id', $sold_item['id'])->first();
                        $sold_Product->sold = true;
                        $sold_Product->properties = $sale_product['properties'];
                        $sold_Product->price = $sale_product['price'];
                        $sold_Product->broker = $sale_product['broker'];
                        $sold_Product->serial = $sale_product['serial'];
                        $sold_Product->stock_name = $sale_product['product']['name'] . " " . $sale_product['properties'];
                        $sold_Product->save();
                        $sold_Product->name = $sale_product['stock_name'];

                        $this->update_stock_quantity($store, $sold_Product->stock_name);

                        $sell_product = $sold_Product;

                        array_push($done_products, $sell_product);
                        $count_ = Stock::where('stock_name', $sale_product['stock_name'])->where('sold', false)->where('store', $store)->count();
                        Stock::where('stock_name', $sale_product['stock_name'])->where('deleted', false)->where('ready',true)->where('sold', false)->where('store', $store)->update([
                            'stock_quantity' => $count_,

                        ]);

                    }


                }
            }
            $sale->delivery = $delivery;


            $sale->sold_items = $done_products;

            $collect = collect($done_products);

            $grouped = $collect->groupBy('name');

            $sale->receipt_stock = $grouped;

            $sale->balance = $sale->sale_total - ($sale->mpesa + $sale->cash - $sum_broker);
            $sale->change = 0;
            if ($sale->balance < 1) {
                $sale->change = ($sale->mpesa + $sale->cash - $sum_broker) - $sale->sale_total;
            }

            return response()->json([
                'status' => true,
                'sale' => $sale
            ]);

        } catch (\Throwable $e) {
            return response()->json([
                'status' => false,
                'message' => 'sale failed',
                'error' => $e->getMessage(),
                'line' => $e->getLine(),
                'trace' => $e->getTrace()

            ], 500);

        }


    }

    public function get_sales(Request $request, $store = null)
    {
        try {
            $sales = Sale::where("store", $store)->with('delivery')->get();

            foreach ($sales as $sale) {
                $served_by = $sale->user_sales()->get('user_name')->first();
                $sold_items = $sale->sale_stock()->get();

                foreach ($sold_items as $sold_item) {
                    $product = Product::where('id', $sold_item->product)->get('name')->first();

                    $sold_item->name = $product->name . ' ' . $sold_item->properties;
                }

                $sale->sold_items = $sold_items;
                $collect = collect($sold_items);

                $grouped = $collect->groupBy('name');

                $sale->receipt_stock = $grouped;


                $sale->served_by = $served_by->user_name;
                $sale->balance = $sale->sale_total - ($sale->mpesa + $sale->cash - $sale->broker_total);
                $sale->change = 0;
                if ($sale->balance < 1) {
                    $sale->change = ($sale->mpesa + $sale->cash - $sale->broker_total) - $sale->sale_total;
                }

            }

            return response()->json([
                'status' => true,
                'data' => $sales
            ], 200);

        } catch (\Throwable $e) {
            return response()->json([
                'status' => false,
                'message' => $e->getMessage()
            ], 500);

        }

    }

    public function get_today_sales(Request $request, $store = null)
    {
        try {
            $sales = Sale::oldest()->where("store", $store)->whereDate('created_at', Carbon::today())->with('delivery')->get();

            foreach ($sales as $sale) {
                $served_by = $sale->user_sales()->get('user_name')->first();
                $sold_items = $sale->sale_stock()->get();
                $items = $sale->sale_stock()->count();
                $sale->items = $items;

                foreach ($sold_items as $sold_item) {
                    $product = Product::where('id', $sold_item->product)->get('name')->first();

                    $sold_item->name = $product->name . ' ' . $sold_item->properties;
                }

                $collect = collect($sold_items);

                foreach ($sold_items as $sold_item_) {
                    $count_ = $collect->countBy('name');
                    $sold_item_->quantity = $count_[$sold_item_->name];
                }

                $sale->sold_items = $sold_items->unique('name');

//                $grouped = $collect->groupBy('name');

//                $sale->receipt_stock = $grouped;


                $sale->served_by = $served_by->user_name;
                $sale->balance = $sale->sale_total - ($sale->mpesa + $sale->cash - $sale->broker_total);
                $sale->change = 0;
                if ($sale->balance < 1) {
                    $sale->change = ($sale->mpesa + $sale->cash - $sale->broker_total) - $sale->sale_total;
                }


            }


            return response()->json([
                'status' => true,
                'data' => $sales
            ], 200);

        } catch (\Throwable $e) {
            return response()->json([
                'status' => false,
                'message' => $e->getMessage()
            ], 500);

        }

    }


    public function get_month_sales(Request $request, $store = null, $month = null)
    {
        try {
            $sales = Sale::where("store", $store)->whereMonth('created_at', $month)->get();

            foreach ($sales as $sale) {
                $served_by = $sale->user_sales()->get('user_name')->first();
                $sold_items = $sale->new_sale_stocks()->get();
                $items = $sale->new_sale_stocks()->count();
                $new_date = Carbon::parse($sale->created_at);
                $sale->sale_date = $new_date->toDateString();
                $sale->items = $items;



                $sale->sold_items = $sold_items;


                $sale->served_by = $served_by->user_name;


            }

            $collect_sales = collect($sales);
            $grouped_sales = $collect_sales->groupBy('sale_date');


            return response()->json([
                'status' => true,
                'data' => $grouped_sales
            ], 200);

        } catch (\Throwable $e) {
            return response()->json([
                'status' => false,
                'message' => $e->getMessage()
            ], 500);

        }

    }

    public function get_sale_products($sale_id = null)
    {
        try {

            $stocks = [];

            $sale_products = SaleProduct::where('sale', $sale_id)->get();
            foreach ($sale_products as $sale_product) {
                $stock_ = Stock::where('id', $sale_product->stock)->first();

                $product_ = Product::find($stock_->product);
                $stock_->product = $product_->name;
                array_push($stocks, $stock_);
            }

            return response()->json([
                'status' => true,

                'products' => $stocks
            ]);

        } catch (\Throwable $e) {
            return response()->json([
                'status' => false,
                'error' => $e->getMessage() . ' ' . $e->getLine()
            ]);
        }
    }

    public function return_sale_product($sale_id = null, $product_id = null)
    {
        try {
            $delete = SaleProduct::where('sale', $sale_id)->where('stock', $product_id)->first();
            if (!$delete) {
                return response()->json([
                    'status' => false,
                    'message' => 'no entry found'
                ]);
            }
            $delete->delete();
            $update_stock = Stock::where('id', $product_id)->first();
            $update_stock->sold = 0;
            $update_stock->broker = 0;
            $update_stock->save();


            return response()->json([
                'status' => true,
                'message' => 'deleted successfully'
            ]);
        } catch (\Throwable $e) {
            return response()->json([
                'status' => false,
                'message' => $e->getMessage()
            ]);

        }

    }

    public function delete_sale($sale_id = null)
    {

        $sale = Sale::where('id', $sale_id)->first();
        $sale_products = SaleProduct::where('sale', $sale_id)->get();
        $sale_delivery = SaleDelivery::where('sale', $sale_id)->first();

        try {

            foreach ($sale_products as $sale_product) {
                $product = Stock::where('id', $sale_product->stock)->first();
                $product->sold = 0;
                $product->broker = 0;
                $product->save();
            }

            $sale_delivery->delete();
            $sale->delete();

            return response()->json([
                'status' => true,
                'message' => 'sale deleted successifully'
            ]);

        } catch (\Throwable  $e) {
            return response()->json([
                'status' => false,
                'error' => $e->getMessage()

            ]);

        }


    }

    public function update_sale(Request $request, $sale_id = null)
    {

        $sale = Sale::where('id', $sale_id)->first();


        $sum_broker = 0;
        $sale_total = 0;

        $sale_products = SaleProduct::where('sale', $sale_id)->get();
        $new_stock = $request->sold_items;

        try {
            if ($sale_products) {

                foreach ($sale_products as $sale_product) {
                    $stock_ = Stock::where('id', $sale_product->stock)->first();
                    $stock_->sold = 0;
                    $stock_->broker = 0;
                    $stock_->save();
                }
                SaleProduct::where('sale', $sale_id)->delete();
            }


            if (count($new_stock)) {
                $done_products = [];
                foreach ($new_stock as $product) {
                    $sale_total += $product["price"];
                    if (isset($product["broker"]))
                        $sum_broker += $product["broker"];
                    $is_sold = Stock::where('id', $product['id'])->first();
                    if ($is_sold->sold) {
                        return response()->json([
                            'status' => false,
                            'message' => 'product out of stock'
                        ], 500);
                    }

                    SaleProduct::create([
                        'sale' => $sale->id,
                        'stock' => $product['id'],
                    ]);

                    $is_sold->sold = true;
                    $is_sold->serial = $product['serial'];
                    $is_sold->properties = $product['properties'];
                    $is_sold->price = $product['price'];
                    $is_sold->broker = $product['broker'];
                    $is_sold->save();
                    $product_name = Product::where('id', $is_sold->product)->get('name')->first();
                    $is_sold->name = $product_name->name . " " . $is_sold->properties;

                    array_push($done_products, $is_sold);

                }

                $delivery = SaleDelivery::where('sale', $sale->id)->first();

                $sale->mpesa = (integer)$request->mpesa;
                $sale->cash = (integer)$request->cash;
                $sale->ref_number = $request->ref_number;
                $sale->broker_total = $sum_broker;
                $sale->sale_total = $sale_total;
                $sale->customer_name = $request->customer_name;
//            $sale->created_at = Carbon::now()->toDateTimeLocalString();
                $sale->save();

                $sale->delivery = $delivery;
                $sale->sold_items = $done_products;

                $collect = collect($done_products);

                $grouped = $collect->groupBy('name');

                $sale->receipt_stock = $grouped;

                $sale->balance = $sale->sale_total - ($sale->mpesa + $sale->cash - $sum_broker);
                $sale->change = 0;
                if ($sale->balance < 1) {
                    $sale->change = ($sale->mpesa + $sale->cash - $sum_broker) - $sale->sale_total;
                }


                return response()->json([
                    'status' => true,
                    'sale' => $sale,
                    'message' => 'sale updated successfully'
                ]);
            }

            $sale->delete();
            return response()->json([
                'status' => true,
                'message' => 'sale deleted successfully'
            ]);


        } catch (\Throwable $e) {
            return response()->json([
                'status' => false,
                'error' => $e->getMessage() . ' ' . 'on line ' . $e->getLine()
            ], 500);
        }

    }

    public function add_date_time_to_sales()
    {

        try {


            $sales = Sale::all();


            foreach ($sales as $sale) {
                $sale->date = Carbon::parse($sale->created_at)->toDateString();
                $sale->time = Carbon::parse($sale->created_at)->toTimeString();
                $sale->save();

            }
            return response()->json([
                'status' => true,
                'message' => 'added date_time to all sales',
            ], 200);

        } catch (\Throwable $e) {
            return response()->json([
                'status' => false,
                'message' => $e->getMessage(),
                'error' => $e->getTrace()

            ], 500);
        }

    }


}
