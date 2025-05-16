<?php

namespace App\Http\Controllers;

use App\Exceptions\InvoicePaymentException;
use App\Models\Customer;
use App\Models\Invoice;
use App\Models\Product;
use App\Models\User;
use App\Models\InvoiceDelivery;
use App\Models\InvoicePayment;
use App\Models\InvoiceProduct;
use App\Models\Stock;
use Carbon\Carbon;

use http\Env\Response;
use Illuminate\Database\Query\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use PHPUnit\Framework\MockObject\Invocation;
use stdClass;

class InvoiceController extends Controller
{

    public function invoice_number()
    {
        $letters = range('A', 'Z');
        $day = Carbon::now()->dayOfWeek;
        $now = Carbon::now();
        $hour = Carbon::now()->hour;
        $end_of_year = Carbon::now()->endOfYear();
        $diff_days = $now->diffInDays($end_of_year, false);

        $num = 0;

        $record_id = Invoice::latest()->get('id')->first();


        if ($record_id) {
            $num = $record_id->id;
        }


        $receipt_num = '#V' . $letters[$day] . $diff_days . $letters[$hour] . $num + 1;

        return $receipt_num;

    }

    public function update_invoice($invoices)
    {
        foreach ($invoices as $invoice) {
            $invoice_total = 0;

            foreach ($invoice->invoice_with_stock as $invoice_stock_) {
                $invoice_total += $invoice_stock_->price;
            }

            Invoice::where('id', $invoice->id)->update([
                'invoice_total' => $invoice_total

            ]);


        }

    }


    public function invoice_delivery_number()
    {
        $letters = range('A', 'Z');
        $day = Carbon::now()->dayOfWeek;
        $now = Carbon::now();
        $hour = Carbon::now()->hour;
        $end_of_year = Carbon::tomorrow()->endOfYear();
        $diff_days = $now->diffInDays($end_of_year, false);

        $deliv = 0;
        $last_delivery = Invoice::latest()->get('id')->first();


        if ($last_delivery) {
            $deliv = $last_delivery->id;
        }
        $delivery_number = '#' . 'inv' . $diff_days . $letters[$hour] . $deliv + 1;

        return $delivery_number;
    }

    public function make_invoice(Request $request, $store = null)
    {


        $validator = Validator::make($request->all(), [
            'products.*.price' => 'required',
//            'products.*.properties' => 'required',
            'customer' => 'required'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'message' => 'validation error',
                'errors' => $validator->errors()
            ], 500);
        }

        try {
            $done_products = [];
            $invoice_total = $request->total;

            $customer = Customer::find($request->customer);

            foreach ($request->products as $invoice_product) {
//                $invoice_total += $invoice_product['price'] * $invoice_product['quantity'];
                $count = Stock::where('store', $store)->where('product', $invoice_product['product_id'])->where('sold', false)->count();

                if (!$count || $count < $invoice_product['quantity']) {
                    return response()->json([
                        'status' => false,
                        'message' => ' product is out of stock'
                    ]);
                }

            }


            if ($customer) {
                $invoice = Invoice::create([
                    'user' => Auth::id(),
                    'store' => $store,
                    'invoice_number' => $this->invoice_number(),
                    'customer' => $customer->id,
                ]);


                $invoice_products = $request->products;
                $invoice_delivery = InvoiceDelivery::create([
                    'invoice' => $invoice->id,
                    'delivery_number' => $this->invoice_delivery_number()
                ]);

                $invoice_delivery_ = $invoice_delivery->delivery_number;

                foreach ($invoice_products as $invoice_product) {
//                    $invoice_total += $invoice_product['price'] * $invoice_product['quantity'];

                    $sold_items = Stock::where('product', $invoice_product['product_id'])->where('sold', false)->limit($invoice_product['quantity'])->get();

                    foreach ($sold_items as $sold_item) {
                        $invoiced_product = InvoiceProduct::create([
                            'invoice' => $invoice->id,
                            'stock' => $sold_item['id'],
                            'picked_at' => Carbon::now()
                        ]);
                        $invoicee_Product = Stock::where('id', $sold_item['id'])->first();
                        $invoicee_Product->sold = true;
                        $invoicee_Product->properties = $invoice_product['properties'];
                        $invoicee_Product->price = $invoice_product['price'];
                        $invoicee_Product->save();
                        $product = Product::where('id', $invoicee_Product->product)->first();
                        $invoicee_Product->name = $product->name . " " . $invoicee_Product->properties;
                        $invoiced_product->stock = $invoicee_Product;
                        array_push($done_products, $invoiced_product);
                    }


                }


                $invoice->invoice_total = $invoice_total;
                $invoice->save();
                $invoice->delivery = $invoice_delivery_;
                $invoice->customer = $customer->name;
                $served_by = User::find($invoice->user)->first();
                $invoice->served_by = $served_by->user_name;


                $invoice->invoice_items = $done_products;
                return response()->json([
                    'status' => true,
                    'data' => $invoice

                ]);


            }
        } catch (\Throwable $e) {
            return response()->json([
                'status' => false,
                'error' => $e->getMessage()
            ], 500);

        }
    }

    public function get_todays_invoices($store)
    {
        try {
            $invoices = Invoice::with('invoice_with_stock')->whereDate('created_at', Carbon::today())->where('store', $store)->get();

            foreach ($invoices as $invoice) {
                $customer = Customer::find($invoice->customer);
                $invoice->customer = $customer;
                $invoice->items = count($invoice->invoice_with_stock);
                $deliverly = InvoiceDelivery::where('invoice', $invoice->id)->get()->pluck('delivery_number');
                $invoice->deliverly = $deliverly;


                $invoice_items = $invoice->invoice_with_stock;

                $collect = collect($invoice_items);

                foreach ($invoice->invoice_with_stock as $invoice_stock_) {
                    $product = Product::find($invoice_stock_->product);
                    $name = $product->name . " " . $invoice_stock_->properties;
                    $invoice_stock_->name = $name;
                }
                $invoice_items = $invoice->invoice_with_stock;

                $collect = collect($invoice_items);



                foreach ($invoice->invoice_with_stock as $invoice_item_) {
                    $count_ = $collect->countBy('name');
                    $invoice_item_->quantity = $count_[$invoice_item_->name];

                }
                $invoice_items = $invoice->invoice_with_stock;
                $invoice['items'] = $invoice_items->unique('name');


            }

            return response()->json([
                'status' => true,
                'data' => $invoices
            ]);


        } catch (\Throwable $e) {
            return response()->json([
                'status' => false,
                'message' => $e->getMessage()
            ]);
        }

    }

    public function get_invoices()
    {
        try {
            $invoices = Invoice::with('invoice_with_stock')->with('payments')->get();
            $new_invoices = [];
            foreach ($invoices as $invoice) {

                $invoice_payed = InvoicePayment::where('invoice', $invoice['id'])->sum('total_payment');
                $served = User::find($invoice->user)->get('user_name')->first();
                $customer_ = Customer::find($invoice->customer)->get('name')->first();
                $invoice->user = $served->user_name;
                $invoice->customer = $customer_->name;
                $invoice->invoice_total = (integer)$invoice->invoice_total;
                $invoice->invoice_payed = (integer)$invoice_payed;
                $invoice->invoice_balance = $invoice->invoice_total - $invoice_payed;
                array_push($new_invoices, $invoice);
            }


            return response()->json([
                'status' => true,
                'data' => $new_invoices
            ]);

        } catch (\Throwable $e) {
            return response()->json([
                'status' => false,
                'message' => $e->getMessage()
            ]);

        }

    }


    public function get_customer_invoice($customer = null)
    {
        try {
            $invoices = Invoice::where('customer', $customer)->whereHas('invoice_with_unreturned_stock')->get();
            $this->update_invoice($invoices);

            $customer_ = Customer::where('id', $customer)->first();
            $total_owed = 0;
            $invoice_details = new stdClass();
            $pedding_invoices = [];
            $balance_after = InvoicePayment::where('customer', $customer)->sum('total_payment');


            $invoice_items__ = [];


            foreach ($invoices as $invoice) {
                $invoice->invoice_with_stock = $invoice->invoice_with_unreturned_stock()->get();

                $invoice_total = 0;

                foreach ($invoice->invoice_with_stock as $invoice_stock_) {

                    $invoice_total += $invoice_stock_->price;
                    $invoice_product_returned = InvoiceProduct::where('stock', $invoice_stock_->id)->get()->first();
                    $product = Product::find($invoice_stock_->product);
                    $name = $product->name . " " . $invoice_stock_->properties;

                    $new_date = Carbon::parse($invoice->created_at);
                    $invoice_stock_->date = $new_date->toDateString();
                    $invoice_stock_->name = $name;
                    $invoice_stock_->returned_invoice = $invoice_product_returned->returned;
                    $invoice_stock_->invoice_number = $invoice->invoice_number;
//                    if($invoice_product_returned->returned){
//                        $invoice->invoice_with_stock->detach($invoice_stock_);
//                    }
                }


                $invoice_payed = InvoicePayment::where('invoice', $invoice['id'])->sum('total_payment');
                $invoice->invoice_payed = (integer)$invoice_payed;
                if ($balance_after - $invoice_total > 0 && $balance_after != 0) {
                    $invoice->invoice_payed = (integer)$invoice_total;
                    $balance_after = (integer)$balance_after - (integer)$invoice_total;
                } elseif ($balance_after == 0) {
                    $invoice->invoice_payed = 0;
                } else {
                    $invoice->invoice_payed = (integer)$balance_after;
                    $balance_after = 0;
                }
                $invoice->balance = (integer)$invoice_total - $invoice->invoice_payed;
                $total_owed += $invoice->balance;
                $invoice->invoice_total = $invoice_total;

                Invoice::where('id', $invoice->id)->update([
                    'invoice_total' => $invoice_total

                ]);


                $invoice_items = $invoice->invoice_with_stock;

                $collect = collect($invoice_items);
                foreach ($invoice->invoice_with_stock as $invoice_item_) {
                    $count_ = $collect->countBy('name');
                    $invoice_item_->quantity = $count_[$invoice_item_->name];
                    array_push($invoice_items__, $invoice_item_);
                }


                $invoice['items'] = $invoice_items->unique('name');
//                $new_date = Carbon::parse($invoice['created_at']);
//                $invoice['date'] = $new_date->toDateString();


                $new__date = Carbon::parse($invoice['created_at']);
                $invoice['date'] = $new__date->toDateString();
                $invoice['combined'] = $invoice['invoice_number'] . " balance : " . $invoice['balance'];

                array_push($pedding_invoices, $invoice);
            }

            $invoice_details->customer_name = $customer_->name;
            $invoice_details->customer_id = $customer_->id;
            $invoice_details->total = $total_owed;
            $collection = collect($invoices);
            $pedding_collection = collect($pedding_invoices);
            $grouped_pedding = $pedding_collection->groupBy('date');
            $grouped = $collection->groupBy('date');

            $collect_invoice_stocks = collect($invoice_items__);

            $grouped_invoice_stocks = $collect_invoice_stocks->groupBy('date');
            $invoice_payments = InvoicePayment::where('customer', $customer)->get();


            return response()->json([
                'status' => true,
                'invoice_details' => $invoice_details,
//                'pedding_invoices' => $grouped_pedding,
                'data' => $grouped,
                'invoice_items' => $grouped_invoice_stocks,
                'invoice_payments' => $invoice_payments

//                "list_pedding" => $pedding_invoices

            ]);


        } catch (\Throwable $e) {
            return response()->json([
                'status' => false,
                'error' => $e->getMessage(),
                'line' => $e->getLine(),
                'file' => $e->getFile(),
                'code' => $e->getTrace()
            ], 500);


        }

    }

    public function make_invoice_payment(Request $request,$store=null, $customer = null)
    {

        $validator = Validator::make($request->all(), [
            'mpesa' => 'required',
            'cash' => 'required',
        ]);


        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'message' => 'validation error',
                'errors' => $validator->errors()
            ], 401);
        }

        try {
            $cheque_date = null;

            if ($request->cheque_date) {
                $cheque_date = Carbon::parse($request->cheque_date)->toDateString();
            }


            $payment = InvoicePayment::create([
                'customer' => $customer,
                'cash' => $request->cash,
                'mpesa' => $request->mpesa,
                'mpesa_ref' => $request->mpesa_ref,
                'total_payment' => $request->cash + $request->mpesa + $request->cheque_amount,
                'cheque_amount' => $request->cheque_amount,
                'bank' => $request->bank,
                'cheque_number' => $request->cheque_number,
                'cheque_date' => $cheque_date,
                'date'=>Carbon::parse(Carbon::now())->toDateString(),
                'store'=>$store,
                'comment'=>$request->comment


            ]);


            return response()->json([
                'status' => true,
                'receipt' => $payment,
                'message' => "Payment Made Successfully"
            ], 200);

        } catch (\Throwable $e) {

            throw  new InvoicePaymentException($e->getMessage(), 500);

        }


    }

    public function return_invoice_item(Request $request, $invoice = null)
    {

        try {
            $stock = $request->stock;
            $stock_ = Stock::where('id', $stock)->where('sold', true)->get()->first();

            InvoiceProduct::where('invoice', $invoice)->where('stock', $stock)->update([
                'returned' => 1,
                'returned_by' => Auth::id(),
                'returned_at' => Carbon::now()
            ]);

            Stock::where('id', $stock)->where('sold', true)->update([
                'sold' => false,
                'broker' => 0
            ]);


            $invoice_ = Invoice::where('id', $invoice)->get()->first();
            $invoice_->invoice_total = $invoice_->invoice_total - $stock_->price;


            $invoice_->save();

//            Invoice::doesntHave('invoice_with_stock')->delete();

            return response()->json([
                'status' => true,
                'message' => 'return success full'


            ]);
        } catch (\Throwable $exception) {

            return response()->json([
                'status' => true,
                'message' => $exception->getMessage()

            ]);


        }


    }

    public function get_debt_summary($store)
    {

        try {
            $invoice = new stdClass();
            $invoice_total = Invoice::where('store', $store)->sum('invoice_total');

            $invoice_payments = 0;
            $store_customers = Customer::where('store', $store)->get('id');

            foreach ($store_customers as $store_customer) {

                $payed_cash = InvoicePayment::where('customer', $store_customer->id)->sum('total_payment');
                $invoice_payments += $payed_cash;
            }

            $pending_bal = $invoice_total - $invoice_payments;


            $invoice->payed = (integer)$invoice_payments;
            $invoice->total = (integer)$invoice_total;
            $invoice->balance = (integer)$pending_bal;


            return response()->json([
                'status' => true,
                'data' => $invoice

            ]);
        } catch (\Throwable $e) {
            return response()->json([
                'status' => false,
                'data' => $e->getMessage()

            ]);

        }


    }

    public function return__stock(Request $request, $customer = null)
    {

        if (!$customer) {
            return response()->json([
                'status' => false,
                'message' => 'Customer Error',

            ], 500);
        }


        $validator = Validator::make($request->all(), [
            'stock' => 'required',
//
        ]);


        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'message' => 'You cannot return 0 items',
                'errors' => $validator->errors()
            ], 401);
        }

        $stocks = $request->stock;

        try {
            foreach ($stocks as $stock) {
                InvoiceProduct::where('stock', $stock)->update([
                    'returned' => true,
                    'returned_at' => Carbon::now(),
                    'returned_by' => Auth::id()

                ]);


                Stock::where('id', $stock)->update([
                    'sold' => 0,
                    'broker' => 0,
                ]);


            }
            $customer__invoices = Invoice::with('invoice_with_unreturned_stock')->where('customer', $customer)->get();


            return response()->json([
                'status' => true,
                'message' => 'Stock Return Successfull'
            ], 200);


        } catch (\Throwable $e) {
            return response()->json([
                'status' => false,
                'line' => $e->getLine(),
                'message' => $e->getMessage(),
                'error' => $e->getTrace()

            ], 500);

        }

    }

    public function delete_take_stock_record(Request $request, $customer = null)
    {

        $validator = Validator::make($request->all(), [
            'stock' => 'required',
//
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'message' => 'You cannot delete 0 items',
                'errors' => $validator->errors()
            ], 401);
        }

        $stocks = $request->stock;

        try {
            foreach ($stocks as $stock) {
                $product = InvoiceProduct::where('stock', $stock)->get()->first();

                $stock_ = Stock::where('id', $stock)->get()->first();

                $stock_->sold = 0;
                $stock_->broker = 0;



                $invoice = Invoice::where('id',$product->invoice)->get()->first();


                $invoice->invoice_total = $invoice->invoice_total - $stock_->price;
                $stock_->save();
                $invoice->save();
                $product->delete();






            }
            return response()->json([
                'status' => true,
                'message' => 'Record Delete Successfull'
            ], 200);

        } catch (\Throwable $e) {
            return response()->json([
                'status' => false,
                'message'=>$e->getMessage(),
                'error' => $e->getTrace()

            ], 500);

        }

    }


}
