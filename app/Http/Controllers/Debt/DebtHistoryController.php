<?php

namespace App\Http\Controllers\Debt;

use App\Http\Controllers\Controller;
use App\Http\Controllers\MyBaseController;
use App\Models\Customer;
use App\Models\DailySummary;
use App\Models\Debt\DebtHistory;
use App\Models\InvoicePayment;
use App\Models\NewInvoice;
use App\Models\NewStock;
use App\Models\Slip;
use App\Models\Stock;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use stdClass;

class DebtHistoryController extends MyBaseController
{
    //

    /*    public function create_new_invoice_stock_names($store)
        {

            try
            {
                DB::table('new_invoices')->where('old_stock', null)->where('store', $store)
                    ->chunkById(500, function (Collection $new_invoices) {
                        foreach ($new_invoices as $new_invoice)
                        {
                            $stock_name = Stock::where('id', $new_invoice->stock)->first('stock_name')->stock_name;
                            DB::table('new_invoices')->where('id', $new_invoice->id)->update([
                                'stock_name' => Str::lower($stock_name)
                            ]);
                        }

                    });


                return response()->json([
                    'status' => true,
                    'message' => 'Stock Name added Successfully',
                ]);
            } catch (\Throwable $e)
            {
                return response()->json([
                    'status' => false,
                    'message' => $e->getMessage(),
                    'error' => $e->getTrace()
                ], 500);
            }


        }*/


    /*    public function move_new_invoices($store)
        {
            try
            {
                DB::table('new_invoices')->where('store', $store)->where('old_stock', null)->where('returned', false)
                    ->chunkById(800, function (Collection $new_invoices) {
                        foreach ($new_invoices as $new_invoice)
                        {
                            $new_stock_id = NewStock::where('stock_name', $new_invoice->stock_name)->first('id')->id;
                            $quantity = NewInvoice::where('stock_name', $new_invoice->stock_name,)->where('customer',
                                $new_invoice->customer)->where('date', $new_invoice->date)->where('returned', false)
                                ->where('price', $new_invoice->price)->count();

                            DB::table('debt_histories')->updateOrInsert([
                                'stock' => $new_stock_id,
                                'user' => $new_invoice->user,
                                'customer' => $new_invoice->customer,
                                'date' => $new_invoice->date,
                                'store' => $new_invoice->store
                            ],
                                [
                                    'quantity' => $quantity,
                                    'price' => $new_invoice->price,
                                    'total_amount' => $quantity * $new_invoice->price,
                                ]

                            );


                        }

                    });
                return response()->json([
                    'status' => true,
                    'message' => "new invoices transferred successfully"
                ]);

            } catch (\Throwable $e)
            {
                return response()->json([
                    'status' => false,
                    'message' => $e->getMessage(),
                    'error' => $e->getTrace()
                ]);
            }


        }*/


//    public function move_old_debts()
//    {
//        try
//        {
//            DB::table('new_invoices')
//                ->where('stock', null)
//                ->where('returned', false)
//                ->chunkById(800, function (Collection $new_invoices) {
//                    foreach ($new_invoices as $new_invoice)
//                    {
//                        DB::table('debt_histories')->updateOrInsert([
//                            'old_stock' => $new_invoice->old_stock,
//                            'user' => $new_invoice->user,
//                            'customer' => $new_invoice->customer,
//                            'date' => $new_invoice->date,
//                            'store' => $new_invoice->store
//                        ],
//                            [
//                                'quantity' => $new_invoice->quantity,
//                                'price' => $new_invoice->price,
//                                'total_amount' => $new_invoice->total,
//                            ]
//
//                        );
//
//
//                    }
//
//                });
//
//            return response()->json([
//                'status' => true,
//                'message' => "new invoices transferred successfully"
//            ]);
//
//
//        } catch (\Throwable $e)
//        {
//            return response()->json([
//                'status' => false,
//                'message' => $e->getMessage(),
//                'error' => $e->getTrace()
//
//            ], 500);
//        }
//
//
//    }



public function create_invoice_lot(){

}

    public function make_new_invoice(Request $request, $store, $customer, $column)
    {
        $this->check_daily_summary($store);

        try
        {
            $stocks = $request->stocks;

            $invoices = [];


            $total = (integer)$request->invoice_total;

            $recent_id = $this->get_recent_summary_id($store);

            DailySummary::find($recent_id)->increment('sales_debt', $total);

            $invoice_lot = $this->create_invoice_lot_number();

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


                $stock_ = $this->get_single_stock($column, $stock['id']);
                $quantity = $stock['sale_quantity'];

                $history->stock = $stock['id'];
                $history->action = 11;
                $history->buying_price = $stock['buying_price'];
                $history->selling_price = $stock['selling_price'];
                $history->quantity = $quantity;
                $history->store = $store;
                $history->previous_stock = $stock_->stock_quantity;
                $history->stock_after = $stock_->stock_quantity - $quantity;
                $history->serial_number = $stock['serial'];


                $invoice_ = DebtHistory::create([
                    'stock' => $stock['id'],
                    'user' => Auth::id(),
                    'customer' => $customer,
                    'date' => Carbon::parse(Carbon::now())->toDateString(),
                    'quantity' => $stock['sale_quantity'],
                    'serial_number' => $stock['serial'],
                    'upgraded_stock_name'=>$stock['upgraded'] ? $stock['upgraded_to']: null,
                    'price' => $stock['selling_price'],
                    'lot_total' =>$total,
                    'invoice_lot'=>$invoice_lot,
                    'total_amount' => $stock['selling_price'] * $stock['sale_quantity'],
                    'store' => $store
                ]);


                $quantity = $stock['sale_quantity'];

                $this->deduct_stock($stock['id'], $column, $quantity);

                $this->make_stock_history($history);


                $invoice_->stock = $stock_;
                array_push($invoices, $invoice_);
            }

            $payments_ = InvoicePayment::where('customer', $customer)->sum('total_payment');
            $slip = Slip::where('customer', $customer)->sum('slip_total');
            $debt_total = DebtHistory::where('customer', $customer)->sum('total_amount');


            $debt_balance = (int)$debt_total - ((int)$payments_ + $slip);

            $now = Carbon::parse(Carbon::now())->format('g:i a jS F Y');


            return response()->json([
                'status' => false,
                'message' => "invoice_made_successfully",
                'invoices' => $invoices,
                'balance_after'=> $debt_balance,
                'balance_before'=>$debt_balance-$total,
                'invoice_total'=>$total,
                'customer' => Customer::find($customer),
                'time'=>$now

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

    public function return_and_restock_new_invoice(Request $request, $store, $column, $customer)
    {
        try
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


            $return_quantity = $request->return_quantity;
            $return_id = $request->return_id;
            $return_amount = $request->return_mount;
            $stock_id = $request->stock_id;

            DB::beginTransaction();

            $stock_ =DB::table('new_stocks')->where('id', $stock_id)
                ->select(DB::raw($column . '  as stock_quantity, id, stock_name,stock_properties,selling_price'))
                ->first();
            $debt = DebtHistory::find($return_id);


            $history->stock = $debt->stock;
            $history->quantity = $return_quantity;
            $history->store = $store;
            $history->returned_from = $customer;
            $history->action = 4;
            $history->previous_stock = $stock_->stock_quantity;
            $history->stock_after = $stock_->stock_quantity + $return_quantity;
            $history->user = Auth::id();


            $this->make_stock_history($history);

            $this->increase_stock($debt->stock, $column, $return_quantity);
            $debt->increment('returned_quantity', $return_quantity);
            $debt->decrement('total_amount', $return_amount);

            DB::commit();

            return response()->json([
                'status' => true,
                'message' => 'return successfull'
            ]);
        } catch (\Throwable $e)
        {
            DB::rollBack();
            return response()->json([
                'status' => false,
                'message' => $e->getMessage(),
                'error' => $e->getTrace()
            ],500);
        }


    }


    public function get_debt_summary($store)
    {
        try
        {
            $total = (integer)$this->get_debt_history_total_debt($store);
            $payments = (integer)$this->get_store_debt_payments($store);
            $customers = $this->get_customers_with_invoices($store);
            return response()->json([
                'status' => 'false',
                'customers' => $customers,
                'debt_receivable' => $total - $payments,
                'debt_received' => $payments,
                'debt_total' => $total

            ]);
        } catch (\Throwable $e)
        {
            return response()->json([
                'status' => false,
                'message' => $e->getMessage(),
                'error' => $e->getTrace(),


            ], 500);
        }


    }

    public function get_customer_all_debts_($customer)
    {

        try
        {

            $payments = $this->get_customer_all_debt_payments($customer);


            $customer_ = Customer::find($customer);

            $payments__ = InvoicePayment::where('customer', $customer)->sum('total_payment');
            $slip = Slip::where('customer', $customer)->sum('slip_total');
            $debt_total = DebtHistory::where('customer', $customer)->where('total_amount','!=',0)->sum('total_amount');

            $customer_->balance = (int)$debt_total - ((int)$payments__ + $slip);
            $customer_->payments_sum_total_payment = $payments__;
            $customer_->debt_sum_total_amount = $debt_total;


            $debt_history_dates = DebtHistory::where('customer', $customer)
                ->where('return', null)->get()->pluck('date')->toArray();
            $slip_dates = Slip::where('customer', $customer)->get()->pluck('slip_date')->toArray();
            $payment_dates = InvoicePayment::where('customer', $customer)->get()->pluck('date')->toArray();

            $all_dates = array_merge($debt_history_dates, $payment_dates, $slip_dates);
            $unique_dates = array_values(array_unique($all_dates));
            $final_array = [];

            rsort($unique_dates);

            foreach ($unique_dates as $unique_date)
            {
                $unique_date_ = new stdClass();
                $unique_date_->invoice_payments = InvoicePayment::where('customer', $customer)
                    ->where('date', $unique_date)->get();
                $invoices = $this->get_customer_all_debts($customer, $unique_date);
                $unique_date_->last_total_debt = DebtHistory::where('customer', $customer)
                    ->whereDate('date', '<', $unique_date)
                    ->sum('total_amount');
                $unique_date_->last_total_payment = InvoicePayment::where('customer', $customer)
                    ->whereDate('date', '<', $unique_date)
                    ->sum('total_payment');
                $unique_date_->today_invoice_payment_total = InvoicePayment::where('customer', $customer)
                    ->whereDate('date', '=', $unique_date)->sum('total_payment');
                $unique_date_->todays_invoices = DebtHistory::where('customer', $customer)
                    ->whereDate('date', '=', $unique_date)->sum('total_amount');

                $unique_date_->todays_slip = Slip::where('customer', $customer)
                    ->where('slip_date', $unique_date)
                    ->sum('slip_total');
                $unique_date_->slips_before = Slip::where('customer', $customer)
                    ->whereDate('slip_date', '<', $unique_date)
                    ->sum('slip_total');

                $unique_date_->debt_before = (integer)$unique_date_->last_total_debt - ($unique_date_->last_total_payment + $unique_date_->slips_before);


                $unique_date_->debt_after = (integer)($unique_date_->last_total_debt + $unique_date_->todays_invoices) -
                    (integer)($unique_date_->today_invoice_payment_total + $unique_date_->last_total_payment +
                        $unique_date_->todays_slip + $unique_date_->slips_before);

                $unique_date_->todays_debt = (integer)$unique_date_->todays_invoices;
                $unique_date_->todays_payments = (integer)$unique_date_->today_invoice_payment_total;
                $unique_date_->todays_slip = (integer)$unique_date_->todays_slip;

                $unique_date_->invoices = $invoices;


                $final_array[$unique_date] = $unique_date_;
            }


            return response()->json([
                'status' => true,
                'invoices' => $final_array,
                'customer' => $customer_,
                'payments' => $payments,
                'dates' => $all_dates

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

    public function get_customer_today_debts_($customer)
    {

        try
        {
            $payments = $this->get_customer_all_debt_payments($customer);

            $customer_ = Customer::find($customer);

            $payments__ = InvoicePayment::where('customer', $customer)->sum('total_payment');
            $slip = Slip::where('customer', $customer)->sum('slip_total');
            $debt_total = DebtHistory::where('customer', $customer)->sum('total_amount');

            $customer_->balance = (int)$debt_total - ((int)$payments__ + $slip);
            $customer_->payments_sum_total_payment = $payments__;
            $customer_->debt_sum_total_amount = $debt_total;

            $final_array = [];

            $unique_date = Carbon::parse(Carbon::now())->toDateString();
            $unique_date_ = new stdClass();
            $unique_date_->invoice_payments = InvoicePayment::where('customer', $customer)
                ->where('date', $unique_date)->get();
            $invoices = $this->get_customer_all_debts($customer, $unique_date);
            $unique_date_->last_total_debt = DebtHistory::where('customer', $customer)
                ->whereDate('date', '<', $unique_date)
                ->sum('total_amount');
            $unique_date_->last_total_payment = InvoicePayment::where('customer', $customer)
                ->whereDate('date', '<', $unique_date)
                ->sum('total_payment');
            $unique_date_->today_invoice_payment_total = InvoicePayment::where('customer', $customer)
                ->whereDate('date', '=', $unique_date)->sum('total_payment');
            $unique_date_->todays_invoices = DebtHistory::where('customer', $customer)
                ->whereDate('date', '=', $unique_date)->sum('total_amount');

            $unique_date_->todays_slip = Slip::where('customer', $customer)
                ->where('slip_date', $unique_date)
                ->sum('slip_total');
            $unique_date_->slips_before = Slip::where('customer', $customer)
                ->whereDate('slip_date', '<', $unique_date)
                ->sum('slip_total');

            $unique_date_->debt_before = (integer)$unique_date_->last_total_debt - ($unique_date_->last_total_payment + $unique_date_->slips_before);


            $unique_date_->debt_after = (integer)($unique_date_->last_total_debt + $unique_date_->todays_invoices) -
                (integer)($unique_date_->today_invoice_payment_total + $unique_date_->last_total_payment +
                    $unique_date_->todays_slip + $unique_date_->slips_before);

            $unique_date_->todays_debt = (integer)$unique_date_->todays_invoices;
            $unique_date_->todays_payments = (integer)$unique_date_->today_invoice_payment_total;
            $unique_date_->todays_slip = (integer)$unique_date_->todays_slip;

            $unique_date_->invoices = $invoices;


            $final_array[$unique_date] = $unique_date_;


            return response()->json([
                'status' => true,
                'invoices' => $final_array,
                'customer' => $customer_,
                'payments' => $payments,


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

    public function get_customer_yesterday_debts_($customer)
    {

        try
        {
            $payments = $this->get_customer_all_debt_payments($customer);

            $customer_ = Customer::find($customer);

            $payments__ = InvoicePayment::where('customer', $customer)->sum('total_payment');
            $slip = Slip::where('customer', $customer)->sum('slip_total');
            $debt_total = DebtHistory::where('customer', $customer)->sum('total_amount');

            $customer_->balance = (int)$debt_total - ((int)$payments__ + $slip);
            $customer_->payments_sum_total_payment = $payments__;
            $customer_->debt_sum_total_amount = $debt_total;

            $final_array = [];

            $unique_date = Carbon::parse(Carbon::yesterday())->toDateString();
            $unique_date_ = new stdClass();
            $unique_date_->invoice_payments = InvoicePayment::where('customer', $customer)
                ->where('date', $unique_date)->get();
            $invoices = $this->get_customer_all_debts($customer, $unique_date);
            $unique_date_->last_total_debt = DebtHistory::where('customer', $customer)
                ->whereDate('date', '<', $unique_date)
                ->sum('total_amount');
            $unique_date_->last_total_payment = InvoicePayment::where('customer', $customer)
                ->whereDate('date', '<', $unique_date)
                ->sum('total_payment');
            $unique_date_->today_invoice_payment_total = InvoicePayment::where('customer', $customer)
                ->whereDate('date', '=', $unique_date)->sum('total_payment');
            $unique_date_->todays_invoices = DebtHistory::where('customer', $customer)
                ->whereDate('date', '=', $unique_date)->sum('total_amount');

            $unique_date_->todays_slip = Slip::where('customer', $customer)
                ->where('slip_date', $unique_date)
                ->sum('slip_total');
            $unique_date_->slips_before = Slip::where('customer', $customer)
                ->whereDate('slip_date', '<', $unique_date)
                ->sum('slip_total');

            $unique_date_->debt_before = (integer)$unique_date_->last_total_debt - ($unique_date_->last_total_payment + $unique_date_->slips_before);


            $unique_date_->debt_after = (integer)($unique_date_->last_total_debt + $unique_date_->todays_invoices) -
                (integer)($unique_date_->today_invoice_payment_total + $unique_date_->last_total_payment +
                    $unique_date_->todays_slip + $unique_date_->slips_before);

            $unique_date_->todays_debt = (integer)$unique_date_->todays_invoices;
            $unique_date_->todays_payments = (integer)$unique_date_->today_invoice_payment_total;
            $unique_date_->todays_slip = (integer)$unique_date_->todays_slip;

            $unique_date_->invoices = $invoices;


            $final_array[$unique_date] = $unique_date_;


            return response()->json([
                'status' => true,
                'invoices' => $final_array,
                'customer' => $customer_,
                'payments' => $payments,


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

    public function get_customer_this_week_debts_($customer)
    {

        try
        {

            $payments = $this->get_customer_all_debt_payments($customer);


            $customer_ = Customer::find($customer);

            $payments__ = InvoicePayment::where('customer', $customer)->sum('total_payment');
            $slip = Slip::where('customer', $customer)->sum('slip_total');
            $debt_total = DebtHistory::where('customer', $customer)->sum('total_amount');

            $customer_->balance = (int)$debt_total - ((int)$payments__ + $slip);
            $customer_->payments_sum_total_payment = $payments__;
            $customer_->debt_sum_total_amount = $debt_total;


            $debt_history_dates = DebtHistory::where('customer', $customer)
                ->whereBetween('date', [Carbon::now()->startOfWeek(), Carbon::now()->endOfWeek()])
                ->where('return', null)
                ->get()
                ->pluck('date')
                ->toArray();
            $slip_dates = Slip::where('customer', $customer)->get()
                ->whereBetween('slip_date', [Carbon::now()->startOfWeek(), Carbon::now()->endOfWeek()])
                ->pluck('slip_date')
                ->toArray();
            $payment_dates = InvoicePayment::where('customer', $customer)
                ->whereBetween('date', [Carbon::now()->startOfWeek(), Carbon::now()->endOfWeek()])
                ->get()
                ->pluck('date')
                ->toArray();

            $all_dates = array_merge($debt_history_dates, $payment_dates, $slip_dates);
            $unique_dates = array_values(array_unique($all_dates));
            $final_array = [];

            rsort($unique_dates);

            foreach ($unique_dates as $unique_date)
            {
                $unique_date_ = new stdClass();
                $unique_date_->invoice_payments = InvoicePayment::where('customer', $customer)
                    ->where('date', $unique_date)->get();
                $invoices = $this->get_customer_all_debts($customer, $unique_date);
                $unique_date_->last_total_debt = DebtHistory::where('customer', $customer)
                    ->whereDate('date', '<', $unique_date)
                    ->sum('total_amount');
                $unique_date_->last_total_payment = InvoicePayment::where('customer', $customer)
                    ->whereDate('date', '<', $unique_date)
                    ->sum('total_payment');
                $unique_date_->today_invoice_payment_total = InvoicePayment::where('customer', $customer)
                    ->whereDate('date', '=', $unique_date)->sum('total_payment');
                $unique_date_->todays_invoices = DebtHistory::where('customer', $customer)
                    ->whereDate('date', '=', $unique_date)->sum('total_amount');

                $unique_date_->todays_slip = Slip::where('customer', $customer)
                    ->where('slip_date', $unique_date)
                    ->sum('slip_total');
                $unique_date_->slips_before = Slip::where('customer', $customer)
                    ->whereDate('slip_date', '<', $unique_date)
                    ->sum('slip_total');

                $unique_date_->debt_before = (integer)$unique_date_->last_total_debt - ($unique_date_->last_total_payment + $unique_date_->slips_before);


                $unique_date_->debt_after = (integer)($unique_date_->last_total_debt + $unique_date_->todays_invoices) -
                    (integer)($unique_date_->today_invoice_payment_total + $unique_date_->last_total_payment +
                        $unique_date_->todays_slip + $unique_date_->slips_before);

                $unique_date_->todays_debt = (integer)$unique_date_->todays_invoices;
                $unique_date_->todays_payments = (integer)$unique_date_->today_invoice_payment_total;
                $unique_date_->todays_slip = (integer)$unique_date_->todays_slip;

                $unique_date_->invoices = $invoices;


                $final_array[$unique_date] = $unique_date_;
            }


            return response()->json([
                'status' => true,
                'invoices' => $final_array,
                'customer' => $customer_,
                'payments' => $payments,
                'dates' => $all_dates

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

    public function get_customer_this_month_debts_($customer)
    {
        try
        {
            $payments = $this->get_customer_all_debt_payments($customer);


            $customer_ = Customer::find($customer);

            $payments__ = InvoicePayment::where('customer', $customer)->sum('total_payment');
            $slip = Slip::where('customer', $customer)->sum('slip_total');
            $debt_total = DebtHistory::where('customer', $customer)->sum('total_amount');

            $customer_->balance = (int)$debt_total - ((int)$payments__ + $slip);
            $customer_->payments_sum_total_payment = $payments__;
            $customer_->debt_sum_total_amount = $debt_total;


            $debt_history_dates = DebtHistory::where('customer', $customer)
                ->whereBetween('date', [Carbon::now()->startOfMonth(), Carbon::now()->endOfMonth()])
                ->where('return', null)
                ->get()
                ->pluck('date')
                ->toArray();
            $slip_dates = Slip::where('customer', $customer)
                ->whereBetween('slip_date', [Carbon::now()->startOfMonth(), Carbon::now()->endOfMonth()])
                ->get()
                ->pluck('slip_date')
                ->toArray();
            $payment_dates = InvoicePayment::where('customer', $customer)
                ->whereBetween('date', [Carbon::now()->startOfMonth(), Carbon::now()->endOfMonth()])
                ->get()
                ->pluck('date')
                ->toArray();

            $all_dates = array_merge($debt_history_dates, $payment_dates, $slip_dates);
            $unique_dates = array_values(array_unique($all_dates));
            $final_array = [];

            rsort($unique_dates);

            foreach ($unique_dates as $unique_date)
            {
                $unique_date_ = new stdClass();
                $unique_date_->invoice_payments = InvoicePayment::where('customer', $customer)
                    ->where('date', $unique_date)->get();
                $invoices = $this->get_customer_all_debts($customer, $unique_date);
                $unique_date_->last_total_debt = DebtHistory::where('customer', $customer)
                    ->whereDate('date', '<', $unique_date)
                    ->sum('total_amount');
                $unique_date_->last_total_payment = InvoicePayment::where('customer', $customer)
                    ->whereDate('date', '<', $unique_date)
                    ->sum('total_payment');
                $unique_date_->today_invoice_payment_total = InvoicePayment::where('customer', $customer)
                    ->whereDate('date', '=', $unique_date)->sum('total_payment');
                $unique_date_->todays_invoices = DebtHistory::where('customer', $customer)
                    ->whereDate('date', '=', $unique_date)->sum('total_amount');

                $unique_date_->todays_slip = Slip::where('customer', $customer)
                    ->where('slip_date', $unique_date)
                    ->sum('slip_total');
                $unique_date_->slips_before = Slip::where('customer', $customer)
                    ->whereDate('slip_date', '<', $unique_date)
                    ->sum('slip_total');

                $unique_date_->debt_before = (integer)$unique_date_->last_total_debt - ($unique_date_->last_total_payment + $unique_date_->slips_before);


                $unique_date_->debt_after = (integer)($unique_date_->last_total_debt + $unique_date_->todays_invoices) -
                    (integer)($unique_date_->today_invoice_payment_total + $unique_date_->last_total_payment +
                        $unique_date_->todays_slip + $unique_date_->slips_before);

                $unique_date_->todays_debt = (integer)$unique_date_->todays_invoices;
                $unique_date_->todays_payments = (integer)$unique_date_->today_invoice_payment_total;
                $unique_date_->todays_slip = (integer)$unique_date_->todays_slip;

                $unique_date_->invoices = $invoices;


                $final_array[$unique_date] = $unique_date_;
            }


            return response()->json([
                'status' => true,
                'invoices' => $final_array,
                'customer' => $customer_,
                'payments' => $payments,
                'dates' => $all_dates

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
}

