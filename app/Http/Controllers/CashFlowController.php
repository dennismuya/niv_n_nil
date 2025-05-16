<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use App\Models\Deposit;
use App\Models\Expense;
use App\Models\Invoice;
use App\Models\InvoiceDelivery;
use App\Models\InvoicePayment;
use App\Models\Product;
use App\Models\Sale;
use App\Models\User;
use Carbon\Carbon;
use DateTime;
use Illuminate\Http\Request;
use stdClass;

class CashFlowController extends Controller
{
    //
    public function cash_deposits($store, $date)
    {
        $date_ = new DateTime($date);
        $date_->format("Y-m-d");
        $deposits_ = Deposit::where('store', $store)->whereDate('created_at', $date_)->sum('amount');
        return (integer)$deposits_;
    }

    public function raw_cash_deposits($store, $date)
    {
        $date_ = new DateTime($date);
        $date_->format("Y-m-d");
        $deposits_ = Deposit::where('store', $store)->whereDate('created_at', $date_)->get();
        return $deposits_;
    }

    public function yesterdays_cash_deposits($store, $date)
    {
        $date_ = new DateTime($date);
        $date_->modify("-1 day");
        $date_->format("Y-m-d");
        $deposits_ = Deposit::where('store', $store, $date)->whereDate('created_at', $date_)->sum('amount');
        return (integer)$deposits_;
    }

    public function yesterdays_raw_cash_deposits($store, $date)
    {
        $date_ = new DateTime($date);
        $date_->modify("-1 day");
        $date_->format("Y-m-d");
        $deposits_ = Deposit::where('store', $store, $date)->whereDate('created_at', $date_)->get();
        return $deposits_;
    }


    public function todays_broker_total($store, $date)
    {
        $date_ = new DateTime($date);
        $date_->format("Y-m-d");

        $broker = Sale::where('store', $store)->where('cash', '>', 0)->whereDate('created_at', $date_)->sum('broker_total');
        return (integer)$broker;

    }

    public function yesterdays_broker_total($store, $date)
    {
        $date_ = new DateTime($date);
        $date_->modify("-1 day");
        $date_->format("Y-m-d");
        $broker = Sale::where('store', $store)->where('cash', '>', 0)->whereDate('created_at', $date_)->sum('broker_total');

        return (integer)$broker;

    }


    public function today_cash_sales($store, $date)
    {
        $date_ = new DateTime($date);
        $date_->format("Y-m-d");
        $total_cash = Sale::where('store', $store)->whereDate('created_at', $date_)->sum('cash');

        return (integer)$total_cash - $this->todays_broker_total($store, $date);
    }


    public function yesterdays_cash_sales($store, $date)
    {
        $date_ = new DateTime($date);
        $date_->modify("-1 day");
        $date_->format("Y-m-d");
        $total_cash = Sale::where('store', $store)->whereDate('created_at', $date_)->sum('cash');

        return (integer)$total_cash - $this->yesterdays_broker_total($store, $date);
    }

    public function get_today_sales($store, $date)
    {

        $date_ = new DateTime($date);
        $date_->format("Y-m-d");

        $sales = Sale::oldest()->where("store", $store)->whereDate('created_at', $date_)->with('delivery')->get();
        $sales_items = [];

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

            foreach ($sale->sold_items as $sold_item) {
                array_push($sales_items, $sold_item);
            }


        }

        return $sales_items;


    }

    public function today_mpesa_sales($store, $date)
    {
        $date_ = new DateTime($date);
        $date_->format("Y-m-d");

        $total_mpesa = Sale::where('store', $store)->whereDate('created_at', $date_)->sum('mpesa');

        return (integer)$total_mpesa;
    }

    public function yesterdays_mpesa_sales($store, $date)
    {
        $date_ = new DateTime($date);
        $date_->modify("-1 day");
        $date_->format("Y-m-d");

        $total_mpesa = Sale::where('store', $store)->whereDate('created_at', $date_)->sum('mpesa');

        return (integer)$total_mpesa;
    }

    public function today_cash_invoice_payments($store, $date)
    {
        $date_ = new DateTime($date);
        $date_->format("Y-m-d");
        $today_invoice_payments_cash = 0;
        $store_customers = Customer::where('store', $store)->get('id');

        foreach ($store_customers as $store_customer) {
            $payed_cash = InvoicePayment::where('customer', $store_customer->id)->whereDate('created_at', $date_)->sum('cash');
            $today_invoice_payments_cash += $payed_cash;
        }
        return (integer)$today_invoice_payments_cash;

    }

    public function today_debt_given($store, $date)
    {
        $date_ = new DateTime($date);
        $date_->format("Y-m-d");
        $total = Invoice::where('store', $store)->whereDate('created_at', $date_)->sum('invoice_total');
        return (integer)$total;
    }

    public function get_todays_invoices($store, $date)
    {
        $date_ = new DateTime($date);
        $date_->format("Y-m-d");

        $invoices = Invoice::with('invoice_with_stock')->whereDate('created_at', $date_)->where('store', $store)->get();
        $invoice_items__ = [];
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
                $invoice_stock_->customer_name = $invoice->customer;

            }

            $invoice_items = $invoice->invoice_with_stock;

            $collect = collect($invoice_items);


            foreach ($invoice->invoice_with_stock as $invoice_item_) {
                $count_ = $collect->countBy('name');
                $invoice_item_->quantity = $count_[$invoice_item_->name];

            }
            $invoice_items = $invoice->invoice_with_stock;
            $invoice['items'] = $invoice_items->unique('name');

            foreach ($invoice['items'] as $item) {
                array_push($invoice_items__, $item);
            }

        }

        return $invoice_items__;


    }

    public function yesterdays_debt_given($store, $date)
    {
        $date_ = new DateTime($date);
        $date_->modify("-1 day");
        $date_->format("Y-m-d");
        $total = Invoice::where('store', $store)->whereDate('created_at', $date_)->sum('invoice_total');
        return (integer)$total;
    }


    public function yesterdays_cash_invoice_payments($store, $date)
    {
        $date_ = new DateTime($date);
        $date_->modify("-1 day");
        $date_->format("Y-m-d");
        $yesterdays_invoice_payments_cash = 0;
        $store_customers = Customer::where('store', $store)->get('id');

        foreach ($store_customers as $store_customer) {
            $payed_cash = InvoicePayment::where('customer', $store_customer->id)->whereDate('created_at', $date_)->sum('cash');
            $yesterdays_invoice_payments_cash += $payed_cash;
        }
        return (integer)$yesterdays_invoice_payments_cash;

    }


    public function today_mpesa_invoice_payments($store, $date)
    {
        $date_ = new DateTime($date);
        $date_->format("Y-m-d");
        $today_invoice_payments_mpesa = 0;
        $store_customers = Customer::where('store', $store)->get('id');

        foreach ($store_customers as $store_customer) {
            $payed_cash = InvoicePayment::where('customer', $store_customer->id)->whereDate('created_at', $date_)->sum('mpesa');
            $today_invoice_payments_mpesa += $payed_cash;
        }
        return (integer)$today_invoice_payments_mpesa;

    }

    public function yesterdays_mpesa_invoice_payments($store, $date)
    {
        $date_ = new DateTime($date);
        $date_->modify("-1 day");
        $date_->format("Y-m-d");
        $yesterdays_invoice_payments_mpesa = 0;
        $store_customers = Customer::where('store', $store)->get('id');
        foreach ($store_customers as $store_customer) {
            $payed_cash = InvoicePayment::where('customer', $store_customer->id)->whereDate('created_at', $date_)->sum('mpesa');
            $yesterdays_invoice_payments_mpesa += $payed_cash;
        }
        return (integer)$yesterdays_invoice_payments_mpesa;

    }

    public function today_invoice_payments_total($store, $date)
    {
        $date_ = new DateTime($date);
        $date_->format("Y-m-d");
        $today_invoice_payments_total = 0;
        $store_customers = Customer::where('store', $store)->get('id');

        foreach ($store_customers as $store_customer) {
            $payed_cash = InvoicePayment::where('customer', $store_customer->id)->whereDate('created_at', $date_)->sum('total_payment');
            $today_invoice_payments_total += $payed_cash;
        }
        return (integer)$today_invoice_payments_total;

    }

    public function todays_invoices_payments($store, $date)
    {
        $date_ = new DateTime($date);
        $date_->format("Y-m-d");
        $today_invoice_payments = [];
        $store_customers = Customer::where('store', $store)->get();

        foreach ($store_customers as $store_customer) {
            $payed_cash = InvoicePayment::where('customer', $store_customer->id)->whereDate('created_at', $date_)->get();

            foreach ($payed_cash as $payed_cash_) {
                $single = [
                    'debtor' => $store_customer->name,
                    'amount' => $payed_cash_->total_payment,
                    'cash' => $payed_cash_->cash,
                    'mpesa' => $payed_cash_->mpesa
                ];

                array_push($today_invoice_payments, $single);

            }


        }


        return $today_invoice_payments;

    }

    public function yesterdays_invoice_payments_total($store, $date)
    {

        $date_ = new DateTime($date);
        $date_->modify("-1 day");
        $date_->format("Y-m-d");

        $yesterdays_invoice_payments_total = 0;
        $store_customers = Customer::where('store', $store)->get('id');

        foreach ($store_customers as $store_customer) {
            $payed_cash = InvoicePayment::where('customer', $store_customer->id)->whereDate('created_at', $date_)->sum('total_payment');
            $yesterdays_invoice_payments_total += $payed_cash;
        }
        return (integer)$yesterdays_invoice_payments_total;

    }

    public function today_expenses($store, $date)
    {
        $date_ = new DateTime($date);
        $date_->format("Y-m-d");
        $daily_expenses = Expense::where('store', $store)->whereDate('created_at', $date_)->sum('amount');
        return (integer)$daily_expenses;

    }

    public function get_expenses($store, $date)
    {
        $date_ = new DateTime($date);
        $date_->format("Y-m-d");

        return Expense::whereDate('created_at', $date_)->where('store', $store)->get();

    }

    public function yesterdays_expenses($store, $date)
    {
        $date_ = new DateTime($date);
        $date_->modify("-1 day");
        $date_->format("Y-m-d");
        $yesterdays_expenses = Expense::where('store', $store)->whereDate('created_at', $date_)->sum('amount');
        return (integer)$yesterdays_expenses;

    }


    public function today_sales_total($store, $date)
    {

        return $this->today_mpesa_sales($store, $date) + $this->today_cash_sales($store, $date);
    }

    public function yesterdays_sales_total($store, $date)
    {


        return $this->yesterdays_cash_sales($store, $date) + $this->yesterdays_mpesa_sales($store, $date);
    }


    public function get_shop_all_cashflow($store = null, $month = null)
    {

        $dates__ = [];
        $days_ = [];
        $days = Carbon::now()->month($month)->daysInMonth;


        $this_month = Carbon::now()->month;

        if ($month == $this_month) {
            $days = Carbon::now()->day;
        }


        for ($i = 1; $i <= $days; $i++) {
            $dt = Carbon::create(Carbon::now()->year, month: $month, day: $i);

            array_push($dates__, $dt->toDateString());

        }

        rsort($dates__);

        $working_dates = $dates__;


        $dates_of_the_month = [];


        foreach ($working_dates as $working_date) {
            $new_ = new stdClass();
            $new_->$working_date = new stdClass();
            $new_->$working_date->expenses = new stdClass();
            $new_->$working_date->sales = new stdClass();
            $new_->$working_date->cash = new stdClass();
            $new_->$working_date->invoice = new stdClass();


//            expenses
            //    today
            $new_->$working_date->expenses->todays_expenses = $this->today_expenses($store, $working_date);
            $new_->$working_date->expenses->expenses = $this->get_expenses($store, $working_date);
//        yesterday
            $new_->$working_date->expenses->yesterdays_expenses = $this->yesterdays_expenses($store, $working_date);

            //        sales
//        today
            $new_->$working_date->sales->daily_total_sales = $this->today_sales_total($store, $working_date);
            $new_->$working_date->sales->daily_sales = $this->get_today_sales($store, $working_date);
            $new_->$working_date->sales->daily_total_with_invoices = $this->today_sales_total($store, $working_date) + $this->today_cash_invoice_payments($store, $working_date);
            $new_->$working_date->sales->daily_mpesa_sales = $this->today_mpesa_sales($store, $working_date);
            $new_->$working_date->sales->cash_sales = $this->today_cash_sales($store, $working_date);
            $new_->$working_date->sales->daily_total = $this->today_sales_total($store, $working_date) + $this->today_invoice_payments_total($store, $working_date);
//        yesterday
            $new_->$working_date->sales->yesterdays_total_sales = $this->yesterdays_sales_total($store, $working_date);
            $new_->$working_date->sales->yesterdays_total_with_invoices = $this->yesterdays_sales_total($store, $working_date) + $this->yesterdays_cash_invoice_payments($store, $working_date);
            $new_->$working_date->sales->yesterdays_mpesa_sales = $this->yesterdays_mpesa_sales($store, $working_date);
            $new_->$working_date->sales->yesterdays_cash_sales = $this->yesterdays_cash_sales($store, $working_date);

            //        cash at hand;
            //        yesterday
            $new_->$working_date->cash->yesterdays_cash_at_hand = ($this->yesterdays_cash_sales($store, $working_date) + $this->yesterdays_cash_invoice_payments($store, $working_date)) - ($this->yesterdays_expenses($store, $working_date) + $this->yesterdays_cash_deposits($store, $working_date));


            //        today
            $new_->$working_date->cash->cash_at_hand = ($this->today_cash_sales($store, $working_date) + $this->today_cash_invoice_payments($store, $working_date)) - ($this->today_expenses($store, $working_date) + $this->cash_deposits($store, $working_date)) + $new_->$working_date->cash->yesterdays_cash_at_hand;
            $new_->$working_date->cash->expenses = $this->today_expenses($store, $working_date);
            $new_->$working_date->cash->yesterdays_expenses = $this->yesterdays_expenses($store, $working_date);


//        bank deposits

//        today
            $new_->$working_date->cash->cash_deposits = $this->cash_deposits($store, $working_date);

//        yesterday
            $new_->$working_date->cash->yesterdays_cash_deposits = $this->yesterdays_cash_deposits($store, $working_date);


            //        invoices
//        today

            $new_->$working_date->invoice->cash_invoice_payments = $this->today_cash_invoice_payments($store, $working_date);
            $new_->$working_date->invoice->mpesa_invoice_payments = $this->today_mpesa_invoice_payments($store, $working_date);
            $new_->$working_date->invoice->total_invoice_payments = $this->today_invoice_payments_total($store, $working_date);
            $new_->$working_date->invoice->today_given_debt = $this->today_debt_given($store, $working_date);
            $new_->$working_date->invoice->todays_invoices = $this->get_todays_invoices($store, $working_date);
            $new_->$working_date->invoice->todays_invoice_payments = $this->todays_invoices_payments($store, $working_date);


//        yesterday
            $new_->$working_date->invoice->yesterdays_cash_invoice_payments = $this->yesterdays_cash_invoice_payments($store, $working_date);
            $new_->$working_date->invoice->yesterdays_mpesa_invoice_payments = $this->yesterdays_mpesa_invoice_payments($store, $working_date);
            $new_->$working_date->invoice->yesterdays_total_invoice_payments = $this->yesterdays_invoice_payments_total($store, $working_date);
            $new_->$working_date->invoice->yesterdays_given_debt = $this->yesterdays_debt_given($store, $working_date);


            $dates_of_the_month[$working_date] = $new_->$working_date;
        }


        return response()->json([
            'status' => true,
            'data' => $dates_of_the_month,
            'days' => $days

        ]);


    }

    public function monthly_cashflow($store = null)
    {
        $months = [6, 7];
        $year = 2023;

        foreach ($months as $month) {

            $year = new stdClass();
            $year = Sale::whereYear('created_at', $year)->whereMonth('created_at');
        }

    }

    public function get_cash_summary($store = null)
    {
        try {
            $store_ = $store;

            $years = [
                [
                'year' => 2024,
                'months' => [  1,2,3,4,5]
            ],
            ];
            $months = [ 9, 10, 11, 12];
            $this_month = Carbon::now()->month;
            $data = [];

            foreach ($years as $year){
                $monthly = new stdClass();
                $monthly->months = [];
                $monthly->year = $year['year'];
                $monthly->year_total = Sale::where('store', $store_)->whereYear('created_at',$monthly->year)->get()->sum('sale_total');
                $monthly->year_total_cash = Sale::where('store', $store_)->whereYear('created_at',$monthly->year)->get()->sum('cash');
                $monthly->year_total_mpesa = $monthly->year_total - $monthly->year_total_cash;


                foreach ($year['months'] as $month) {


                    if ($month <= $this_month) {

                        $monthly->months[$month] = new stdClass();
                        $total_sales = Sale::where('store', $store_)->whereMonth('created_at',$month)->get()->sum('sale_total');
                        $cash = Sale::where('store', $store_)->whereMonth('created_at',$month)->get()->sum('cash');
                        $mpesa = $total_sales - $cash;
                        $monthly->months[$month]->total_sales = (integer)$total_sales;
                        $monthly->months[$month]->cash_sales = (integer)$cash;
                        $monthly->months[$month]->mpesa_sales = (integer)$mpesa;
                    }

                }
                array_push($data,$monthly);

            }


            return response()->json([
                'status' => true,
                'data' => $data

            ]);
        } catch (\Throwable $e) {
            return response()->json([
                'status' => false,
                'error' => $e->getMessage(),
                'line' => $e->getLine(),
                'file' => $e->getFile()

            ]);
        }


    }


}
