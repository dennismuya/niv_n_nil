<?php

namespace App\Http\Controllers;

use App\Exceptions\InvoicePaymentException;
use App\Models\Customer;
use App\Models\DailySummary;
use App\Models\Invoice;
use App\Models\InvoiceDelivery;
use App\Models\InvoicePayment;
use App\Models\InvoiceProduct;
use App\Models\NewInvoice;
use App\Models\oldDebt;
use App\Models\OldStock;
use App\Models\Product;
use App\Models\Slip;
use App\Models\Stock;
use App\Models\SupplyPayment;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use stdClass;

class NewInvoiceController extends MyBaseController
{
    //

    public function move_invoices($store = null)
    {

//        NewInvoice::truncate();

        try {


            $invoice_stocks = InvoiceProduct::with(['invoice' => [
                'customer'
            ],])->with(['stock' => ['product']])->get()->toArray();


            $result = $invoice_stocks;


            foreach ($result as $invoice_stock) {
                $new_date = Carbon::parse($invoice_stock['created_at']);


                NewInvoice::create([
                    'customer' => $invoice_stock['invoice']['customer']['id'],
                    'store' => $invoice_stock['invoice']['customer']['store'],
                    'user' => Auth::id(),
                    'stock' => $invoice_stock['stock']['id'],
                    'date' => $new_date->toDateString(),
                    'price' => $invoice_stock['stock']['price'] ? $invoice_stock['stock']['price'] : 0,
                    'quantity' => 1,
                    'total' => $invoice_stock['stock']['price'] ? $invoice_stock['stock']['price'] : 0,
                    'returned' => $invoice_stock['returned'],
                    'returned_date' => $invoice_stock['returned_at'],
                    'returned_by' => $invoice_stock['returned_by'],
                    'picked_by' => $invoice_stock['invoice']['items_picked_by'],
                ]);
            }
            return response()->json([
                'status' => true,
                'result' => $invoice_stocks,
//                'data' => 'successful Dennis Muya'
            ], 200);


        } catch (\Throwable $e) {
            return response()->json([
                'status' => false,
                'error' => $e->getMessage(),
                'line' => $e->getLine(),
                'trace' => $e->getTrace()
            ], 500);


        }


    }

    public function make_new_invoice(Request $request, $store = null)
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
            $invoice_total = 0;

            $customer = Customer::find($request->customer);


            if ($customer) {

                $invoice_products = $request->products;

                foreach ($invoice_products as $invoice_product) {

                    $count = Stock::where('store', $store)->where('product', $invoice_product['product']['id'])->where('sold', false)->count();

                    if (!$count || $count < $invoice_product['quantity']) {
                        return response()->json([
                            'status' => false,
                            'message' => ' product is out of stock'
                        ]);
                    }
                    $sold_items = Stock::where('store', $store)->where('product', $invoice_product['product']['id'])->where('sold', false)->limit($invoice_product['quantity'])->get();

                    foreach ($sold_items as $sold_item) {
                        $invoicee_Product = Stock::where('id', $sold_item['id'])->first();
                        $invoicee_Product->sold = true;
                        $invoicee_Product->properties = $invoice_product['properties'];
                        $invoicee_Product->price = $invoice_product['price'];
                        $invoicee_Product->save();
                        $new_date = Carbon::parse(Carbon::now());

                        Stock::where('stock_name',$sold_item['stock_name'])->update([
                            'stock_quantity'=>$sold_item['stock_quantity'] - $invoice_product['quantity']
                        ]);

                        NewInvoice::create([
                            'customer' => $customer->id,
                            'store' => $store,
                            'user' => Auth::id(),
                            'stock' => $invoicee_Product->id,
                            'date' => $new_date->toDateString(),
                            'price' => $invoice_product['price'],
                            'quantity' => 1,
                            'total' => $invoice_product['price'],
                            'returned' => false,
                        ]);

                        $product = Product::where('id', $invoicee_Product->product)->get()->first();
                        $invoicee_Product->name = $product->name . " " . $invoicee_Product->properties;
                        array_push($done_products, $invoicee_Product);
                    }

                }

                return response()->json([
                    'status' => true,
                    'invoice_products' => $done_products,
                    'customer' => $customer
                ], 200);

            }
        } catch (\Throwable $e) {
            return response()->json([
                'status' => false,
                'error' => $e->getMessage(),
                'trace' => $e->getTrace()
            ], 500);

        }
    }

    public function get_customers_with_new_invoices($store = null)
    {
        $total_payment_ = 0;

        try {
            $data = Customer::whereHas('new_invoices')->where('store', $store)->with('supplies')->get();

            $customers_with_pedding = [];


            foreach ($data as $customer) {
                $balance_after = InvoicePayment::where('customer', $customer->id)->sum('total_payment');
                $total_invoices_debt = NewInvoice::where('customer', $customer->id)->where('returned', false)->sum('total');
                $total_slip = Slip::where('customer', $customer->id)->where('store', $store)->sum('slip_total');

                $customer_supply_total = 0;
                foreach ($customer->supplies as $supply) {
                    $customer_supply_total += $supply->buying_price;
                }

                $customer->supply_total = $customer_supply_total - $total_slip;


                $customer->pending_balance = (integer)$total_invoices_debt - ($balance_after + $total_slip);


                if ($customer->pending_balance > 0) {
                    array_push($customers_with_pedding, $customer);
                }
            }

            return response()->json([
                'data' => $data
            ]);

        } catch (\Throwable $exception) {
            return response()->json([
                'data' => $exception->getMessage()
            ]);

        }

    }

    public function get_customer_new_invoice($store = null, $customer = null)
    {

        try {

                $new_invoice_dates = NewInvoice::where('customer', $customer)->where('returned', false)->where('debt_balanced', false)->get()->pluck('date')->toArray();
                $new_slip_dates = Slip::where('customer', $customer)->where('debt_balanced', false)->get()->pluck('slip_date')->toArray();
                $customer_payment_dates = InvoicePayment::where('customer', $customer)->where('debt_balanced', false)->get()->pluck('date')->toArray();


                $all_dates = array_merge($new_invoice_dates, $customer_payment_dates, $new_slip_dates);

                $unique_dates = array_values(array_unique($all_dates));

                $final_array = [];

            sort($unique_dates);

                foreach ($unique_dates as $unique_date) {

                    $unique_date_ = new stdClass();

                    $unique_date_->invoice_payments = InvoicePayment::where('customer', $customer)->where('debt_balanced', false)->where('date', $unique_date)->get();

                    $invoices = NewInvoice::where('customer', $customer)->where('debt_balanced', false)->where('date', $unique_date)->where('returned', false)->with('old_stock')->with([
                        "stock" => [
                            "product"
                        ]
                    ])->get();

                    foreach ($invoices as $invoice_) {
                        if ($invoice_['old_stock']) {
                            $oldstock = OldStock::where('id', $invoice_['old_stock'])->first();
                            $name_ = $oldstock->name;
                            $invoice_->name = $name_;

                        } else {
                            $stock = Stock::where('id', $invoice_['stock'])->first();
                            $product_name = Product::where('id', $stock->product)->first();
                            $name = $product_name->name . " " . $stock->properties;
                            $invoice_->name = $name;
                        }

                    }


                    $last_total_debt = NewInvoice::where('customer', $customer)->whereDate('date', '<', $unique_date)->where('debt_balanced', false)->where('returned', false)->sum('total');
                    $last_total_payment = InvoicePayment::where('customer', $customer)->where('debt_balanced', false)->whereDate('date', '<', $unique_date)->sum('total_payment');


                    $today_invoice_payment_total = InvoicePayment::where('customer', $customer)->where('debt_balanced', false)->whereDate('date', '=', $unique_date)->sum('total_payment');
                    $todays_invoices = NewInvoice::where('customer', $customer)->where('debt_balanced', false)->where('returned', false)->where('date', $unique_date)->sum('total');
                    $todays_slip = Slip::where('customer', $customer)->where('debt_balanced', false)->where('store', $store)->where('slip_date', $unique_date)->sum('slip_total');
                    $slips_before = Slip::where('customer', $customer)->where('debt_balanced', false)->where('store', $store)->whereDate('slip_date', '<', $unique_date)->sum('slip_total');

                    $unique_date_->debt_before = (integer)$last_total_debt - ($last_total_payment + $slips_before);

                    $unique_date_->debt_after = (integer)($last_total_debt + $todays_invoices) - (integer)($today_invoice_payment_total + $last_total_payment + $todays_slip + $slips_before);
                    $unique_date_->todays_debt = (integer)$todays_invoices;
                    $unique_date_->todays_payments = (integer)$today_invoice_payment_total;
                    $unique_date_->todays_slip = (integer)$todays_slip;


                    $unique_date_->invoices = $invoices;


                    $final_array[$unique_date] = $unique_date_;
                }


                $payments = InvoicePayment::where('customer', $customer)->sum('total_payment');

                $customer_ = Customer::find($customer);
                $total_owed = NewInvoice::where('store', $store)->where('debt_balanced', false)->where('returned', 0)->where('customer', $customer)->sum('total');
                $invoice_details = new stdClass();

                $balance_after = InvoicePayment::where('customer', $customer)->where('debt_balanced', false)->sum('total_payment');
                $invoice_items__ = [];


                $total_invoices_debt = NewInvoice::where('customer', $customer)->where('debt_balanced', false)->where('returned', false)->sum('total');
                $total_slip = Slip::where('customer', $customer)->where('store', $store)->where('debt_balanced', false)->sum('slip_total');


                $invoice_details->customer_name = $customer_->name;
                $invoice_details->customer_id = $customer_->id;
                $invoice_details->total = (integer)$total_invoices_debt - ($balance_after + $total_slip);
                $invoice_payments = InvoicePayment::where('customer', $customer)->where('debt_balanced', false)->get();
                $col = collect($invoice_items__);
                $grouped = $col->groupBy('date');

                return response()->json([
                    'status' => true,
                    'invoice_details' => $invoice_details,
                    'final_data' => $final_array,
                    'invoice_payments' => $invoice_payments


                ]);




        } catch (\Throwable $e) {
            return response()->json([
                'status' => false,
                'error' => $e->getMessage(),
                'line' => $e->getLine(),
                'trace' => $e->getTrace()
            ], 500);

        }

    }

    public function get_customer_new_invoice_with_archives($store = null, $customer = null) {

        try {

            $new_invoice_dates = NewInvoice::where('customer', $customer)->where('returned', false)->get()->pluck('date')->toArray();
            $new_slip_dates = Slip::where('customer', $customer)->get()->pluck('slip_date')->toArray();
            $customer_payment_dates = InvoicePayment::where('customer', $customer)->get()->pluck('date')->toArray();


            $all_dates = array_merge($new_invoice_dates, $customer_payment_dates, $new_slip_dates);

            $unique_dates = array_values(array_unique($all_dates));

            $final_array = [];

            sort($unique_dates);

            foreach ($unique_dates as $unique_date) {

                $unique_date_ = new stdClass();

                $unique_date_->invoice_payments = InvoicePayment::where('customer', $customer)->where('date', $unique_date)->get();

                $invoices = NewInvoice::where('customer', $customer)->where('date', $unique_date)->where('returned', false)->with('old_stock')->with([
                    "stock" => [
                        "product"
                    ]
                ])->get();

                foreach ($invoices as $invoice_) {
                    if ($invoice_['old_stock']) {
                        $oldstock = OldStock::where('id', $invoice_['old_stock'])->first();
                        $name_ = $oldstock->name;
                        $invoice_->name = $name_;

                    } else {
                        $stock = Stock::where('id', $invoice_['stock'])->first();
                        $product_name = Product::where('id', $stock->product)->first();
                        $name = $product_name->name . " " . $stock->properties;
                        $invoice_->name = $name;
                    }

                }


                $last_total_debt = NewInvoice::where('customer', $customer)->whereDate('date', '<', $unique_date)->where('returned', false)->sum('total');
                $last_total_payment = InvoicePayment::where('customer', $customer)->whereDate('date', '<', $unique_date)->sum('total_payment');


                $today_invoice_payment_total = InvoicePayment::where('customer', $customer)->whereDate('date', '=', $unique_date)->sum('total_payment');
                $todays_invoices = NewInvoice::where('customer', $customer)->where('returned', false)->where('date', $unique_date)->sum('total');
                $todays_slip = Slip::where('customer', $customer)->where('store', $store)->where('slip_date', $unique_date)->sum('slip_total');
                $slips_before = Slip::where('customer', $customer)->where('store', $store)->whereDate('slip_date', '<', $unique_date)->sum('slip_total');

                $unique_date_->debt_before = (integer)$last_total_debt - ($last_total_payment + $slips_before);

                $unique_date_->debt_after = (integer)($last_total_debt + $todays_invoices) - (integer)($today_invoice_payment_total + $last_total_payment + $todays_slip + $slips_before);
                $unique_date_->todays_debt = (integer)$todays_invoices;
                $unique_date_->todays_payments = (integer)$today_invoice_payment_total;
                $unique_date_->todays_slip = (integer)$todays_slip;


                $unique_date_->invoices = $invoices;


                $final_array[$unique_date] = $unique_date_;
            }


            $payments = InvoicePayment::where('customer', $customer)->sum('total_payment');

            $customer_ = Customer::find($customer);
            $total_owed = NewInvoice::where('store', $store)->where('returned', 0)->where('customer', $customer)->sum('total');
            $invoice_details = new stdClass();

            $balance_after = InvoicePayment::where('customer', $customer)->sum('total_payment');
            $invoice_items__ = [];


            $total_invoices_debt = NewInvoice::where('customer', $customer)->where('returned', false)->sum('total');
            $total_slip = Slip::where('customer', $customer)->where('store', $store)->sum('slip_total');


            $invoice_details->customer_name = $customer_->name;
            $invoice_details->customer_id = $customer_->id;
            $invoice_details->total = (integer)$total_invoices_debt - ($balance_after + $total_slip);
            $invoice_payments = InvoicePayment::where('customer', $customer)->get();
            $col = collect($invoice_items__);
            $grouped = $col->groupBy('date');

            return response()->json([
                'status' => true,
                'invoice_details' => $invoice_details,
                'final_data' => $final_array,
                'invoice_payments' => $invoice_payments


            ]);




        } catch (\Throwable $e) {
            return response()->json([
                'status' => false,
                'error' => $e->getMessage(),
                'line' => $e->getLine(),
                'trace' => $e->getTrace()
            ], 500);

        }

    }


    public function old_debt(Request $request, $store = null, $customer = null)
    {

        try {

            foreach ($request->stock as $item) {

                $stock = OldStock::create([
                    'name' => $item['item'],
                    'date' => $item['Date'],
                    'price' => $item['each']

                ]);

                if ($stock) {
                    NewInvoice::create([
                        'customer' => $customer,
                        'store' => $store,
                        'user' => Auth::id(),
                        'old_stock' => $stock->id,
                        'date' => $item['Date'],
                        'price' => $item['each'],
                        'quantity' => $item['quantity'],
                        'total' => $item['total'],
                        'returned' => false,
                    ]);
                }
            }

            return response()->json([
                'status' => true,
                'message' => 'Good work Dennis'
            ], 200);
        } catch (\Throwable $e) {
            return response()->json([
                'status' => false,
                'error' => $e->getMessage(),
                'line' => $e->getLine()
            ], 500);
        }


    }


    public function old_invoices_without_stock()
    {

        $data = Invoice::doesnthave('stock')->get();
        return response()->json([
            'data' => $data

        ]);
    }


    public function return_stock(Request $request, $customer = null)
    {

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
                $invoice = NewInvoice::where('id', $stock)->where('customer', $customer)->where('returned', false)->get()->first();

                $invoice->returned = true;
                $invoice->total = 0;
                $invoice->returned_date = Carbon::now();
                $invoice->returned_by = Auth::id();

                $stock__ = Stock::find( $invoice->stock);

                    $stock__->sold =0;
                    $stock__->broker = 0;

                Stock::where('stock_name', $stock__->stock_name)->update([
                    'stock_quantity'=>$stock__->stock_quantity + 1
                ]);

                  $stock__->save();
                $invoice->save();

            }
            return response()->json([
                'status' => true,
                'message' => 'Stock Return Successful'
            ], 200);

        } catch (\Throwable $e) {
            return response()->json([
                'status' => false,
                'message'=> $e->getMessage(),
                'error' => $e->getTrace()

            ], 500);

        }

    }

    public function delete_new_invoice_stock_record(Request $request, $customer = null)
    {

        $validator = Validator::make($request->all(), [
            'stock' => 'required',

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
                $invoice = NewInvoice::where('id', $stock)->where('customer', $customer)->where('returned', false)->get()->first();
                Stock::where('id', $invoice->stock)->update([
                    'sold' => 0,
                    'broker' => 0,
                ]);

                NewInvoice::where('id', $stock)->where('customer', $customer)->delete();

            }
            return response()->json([
                'status' => true,
                'message' => 'Record Delete Successfull'
            ], 200);

        } catch (\Throwable $e) {
            return response()->json([
                'status' => false,
                'error' => $e->getTrace()

            ], 500);

        }

    }


    public function clear_dairy_summary()
    {
        try {
            $del = DailySummary::truncate();
            return response()->json([
                'status' => true,
                'data' => $del
            ]);
        } catch (\Throwable $e) {
            return response()->json([
                'status' => false,
                'error_message' => $e->getMessage(),
                'error' => $e->getTrace()
            ]);
        }
    }

    public function get_new_invoice_summary($store)
    {

        try {
            $invoice = new stdClass();
            $invoice_total = NewInvoice::where('store', $store)->where('returned', false)->sum('total');

            $invoice_payments = InvoicePayment::where('store', $store)->sum('total_payment');


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


    public function add_store_to_invoice_payments()
    {

        try {

            $invoice_payments = InvoicePayment::all();
            foreach ($invoice_payments as $invoice_payment) {
                $customer = Customer::find($invoice_payment->customer);

                $invoice_payment->store = $customer->store;

                $invoice_payment->save();

            }

            return response()->json([
                'status' => true,
                'message' => 'good work dennis'
            ]);


        } catch (\Throwable $e) {

            return response()->json([
                'status' => false,
                'error' => $e->getTrace(),
                'message' => $e->getMessage()

            ]);
        }


    }


    public function add_old_payments(Request $request, $store = null, $customer = null)
    {
        try {
            $payments = $request->payments;

            foreach ($payments as $payment) {

                InvoicePayment::create([
                    'customer' => $customer,
                    'date' => $payment['Date'],
                    'total_payment' => $payment['Amount'],
                    'comment' => $payment['Comment'],
                    'store' => $store
                ]);

            }

            return response()->json([
                'status' => true,
                'message' => 'client payments updated'
            ]);
        } catch (\Throwable $e) {
            return response()->json([
                'status' => false,
                'error' => $e->getTrace(),
                'message' => $e->getMessage()
            ]);

        }


    }


//    pay new invoice
    public function make_new_invoice_payment(Request $request, $store = null, $customer = null)
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
                'date' => Carbon::parse(Carbon::now())->toDateString(),
                'store' => $store,
                'comment' => $request->comment


            ]);

            $total_ = $request->cash + $request->mpesa + $request->cheque_amount;

            $recent_id = $this->get_recent_summary_id($store);

            DailySummary::find($recent_id)->increment('debt_recovered_cash', $request->cash);
            DailySummary::find($recent_id)->increment('debt_recovered_mpesa', $request->mpesa);
            DailySummary::find($recent_id)->increment('debt_recovered_total', $total_);
            DailySummary::find($recent_id)->increment('closing_balance', $request->cash);



            return response()->json([
                'status' => true,
                'receipt' => $payment,
                'message' => "Payment Made Successfully"
            ], 200);

        } catch (\Throwable $e) {

            throw  new InvoicePaymentException($e->getMessage(), 500);

        }


    }


//    archive invoices


    public function archive_invoices(Request $request, $store = null, $customer = null)
    {

        $date = $request->date;

        try {
            NewInvoice::where('customer', $customer)->where('store', $store)->whereDate('date', '<=', $date)->update([
                'debt_balanced' => true,
                'debt_balanced_by' => Auth::id(),
                'debt_balance_date' => Carbon::parse(Carbon::now())->toDateString()

            ]);
            SupplyPayment::where('customer', $customer)->where('store', $store)->whereDate('date', '<=', $date)->update([
                'debt_balanced' => true,
                'debt_balanced_by' => Auth::id(),
                'debt_balance_date' => Carbon::parse(Carbon::now())->toDateString()

            ]);
            InvoicePayment::where('customer', $customer)->where('store', $store)->whereDate('date', '<=', $date)->update([
                'debt_balanced' => true,
                'debt_balanced_by' => Auth::id(),
                'debt_balance_date' => Carbon::parse(Carbon::now())->toDateString()

            ]);
            Slip::where('customer', $customer)->where('store', $store)->whereDate('slip_date', '<=', $date)->update([
                'debt_balanced' => true,
                'debt_balanced_by' => Auth::id(),
                'debt_balance_date' => Carbon::parse(Carbon::now())->toDateString()

            ]);


            return response()->json([
                'status' => true,
                'message' => 'Archive Successful'

            ], 200);

        } catch (\Throwable $e
        ) {
            return response()->json([
                'status' => false,
                'message' => 'an error occured'

            ], 500);
        }


    }


}
