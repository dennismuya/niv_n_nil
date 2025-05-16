<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use App\Models\DailySummary;
use App\Models\Deposit;
use App\Models\Expense;
use App\Models\Invoice;
use App\Models\InvoiceDelivery;
use App\Models\InvoicePayment;
use App\Models\NewInvoice;
use App\Models\Product;
use App\Models\Sale;
use Carbon\Carbon;
use DateTime;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;

class DailySummaryController extends Controller
{



    public function todays_broker_total($store, $date)
    {


        $broker = Sale::where('store', $store)->where('cash', '>', 0)->whereDate('date', $date)->sum('broker_total');
        return (integer)$broker;

    }
    public function today_cash_sales($store, $date)
    {
        $total_cash = Sale::where('store', $store)->whereDate('date', $date)->sum('cash');

        return (integer)$total_cash - $this->todays_broker_total($store, $date);
    }




   public  function update_daily_summary($store)
   {


       $invoice_payment_total = InvoicePayment::whereDate('date', Carbon::parse(Carbon::today())->toDateString())->where('store', $store)->sum('total_payment');
       $invoice_payment_mpesa_total = InvoicePayment::whereDate('date', Carbon::parse(Carbon::today())->toDateString())->where('store', $store)->sum('mpesa');
       $invoice_payment_cash_total =  InvoicePayment::whereDate('date', Carbon::parse(Carbon::today())->toDateString())->where('store', $store)->sum('cash');
       $today_sale_total = Sale::whereDate('date', Carbon::parse(Carbon::today())->toDateString())->where('store', $store)->sum('sale_total');
       $today_sale_mpesa = Sale::whereDate('date', Carbon::parse(Carbon::today())->toDateString())->where('store', $store)->sum('mpesa');

       $cash_without_mpesa = Sale::whereDate('date', Carbon::parse(Carbon::today())->toDateString())->where('store', $store)->where('mpesa','=', 0)->sum('sale_total');
       $cash_with_mpesa = Sale::whereDate('date', Carbon::parse(Carbon::today())->toDateString())->where('store', $store)->where('mpesa','!=', 0)->sum('cash');

       $broker_total = Sale::whereDate('date', Carbon::parse(Carbon::today())->toDateString())->where('store', $store)->where('mpesa','=',0)->sum('broker_total');
       $cash_sales =  Sale::whereDate('date', Carbon::parse(Carbon::today())->toDateString())->where('store', $store)->sum('cash');
       $today_sale_cash = $this->today_cash_sales($store,Carbon::parse(Carbon::today())->toDateString());
       $sales_debt = NewInvoice::whereDate('date', Carbon::parse(Carbon::today())->toDateString())->where('store', $store)->where('returned', false)->sum('total');
       $expenses = Expense::whereDate('date', Carbon::parse(Carbon::today())->toDateString())->where('store', $store)->sum('amount');
       $opening_balance = DailySummary::whereDate('date', Carbon::parse(Carbon::today())->toDateString())->where('store', $store)->get()->pluck('opening_balance');
       $bank_deposits = Deposit::whereDate('date', Carbon::parse(Carbon::today())->toDateString())->where('store', $store)->sum('amount');
       $closing_balance = ((integer)$invoice_payment_cash_total + $opening_balance[0] + $today_sale_cash) - ($expenses + $bank_deposits);

       $todays_summary = DailySummary::whereDate('date', Carbon::parse(Carbon::today())->toDateString())->where('store', $store)->get()->first();

       $todays_summary->sales_cash = $today_sale_cash ? (integer)$today_sale_cash : 0;
       $todays_summary->sales_mpesa = $today_sale_mpesa ? (integer)$today_sale_mpesa : 0;
       $todays_summary->sales_total = $today_sale_total ? (integer)$today_sale_total : 0;
       $todays_summary->sales_debt = $sales_debt ? (integer)$sales_debt : 0 ;
       $todays_summary->expenses = $expenses ? (integer)$expenses : 0 ;
       $todays_summary->debt_recovered_cash = $invoice_payment_cash_total ? (integer)$invoice_payment_cash_total : 0 ;
       $todays_summary->debt_recovered_mpesa = $invoice_payment_mpesa_total ? (integer)$invoice_payment_mpesa_total : 0 ;
       $todays_summary->debt_recovered_total = $invoice_payment_total ? (integer)$invoice_payment_total : 0;

       $todays_summary->bank_deposits = $bank_deposits ? (integer)$bank_deposits : 0;
       $todays_summary -> closing_balance = $closing_balance ? (integer)$closing_balance : 0;

       $todays_summary->save();

}

//    set today

public  function set_today($store=null){



    $find_ = DailySummary::where('store',$store)->whereDate('date',Carbon::parse(Carbon::today())->toDateString())->get()->first();

    if(!$find_) {
        $date = Carbon::now();
        $day = Carbon::parse($date);


            $date_ = new DateTime($date->toDateString());
            $date_->modify("-1 day");
            $date_->format("Y-m-d");

            $yesterdaysato_closing_balance = DailySummary::whereDate('date', $date_)->where('store', $store)->get()->pluck('closing_balance');
            $yesterdays_closing_balance_ = DailySummary::whereDate('date', Carbon::parse(Carbon::yesterday())->toDateString())->where('store', $store)->get()->pluck('closing_balance');

            $closing = $day->isMonday() ? $yesterdaysato_closing_balance :$yesterdays_closing_balance_;

            DailySummary::create([
                'date' => Carbon::parse(Carbon::today())->toDateString(),
                'opening_time' => Carbon::parse(Carbon::now())->toDateTimeLocalString(),
                'store' => $store,
                'opening_balance' => count($closing) ? (integer)$closing[0] : 0,
            ]);

    }
}


public function get_todays_summary($store=null){
    try {

        $check_today = DailySummary::where('store',$store)->whereDate('date',Carbon::parse(Carbon::today())->toDateString())->get()->first();

        if(!$check_today){
            $this->set_today($store);
            $date = Carbon::now();
            $day = Carbon::parse($date);

            $date_ = new DateTime($date->toDateString());
            $date_->modify("-1 day");
            $date_->format("Y-m-d");

            $check_yesterday_ = DailySummary::where('store',$store)->whereDate('date',Carbon::parse(Carbon::yesterday())->toDateString())->get()->first();
            $check_saturday = DailySummary::where('store',$store)->whereDate('date',$date_)->get()->first();

            $check_today_ = DailySummary::where('store',$store)->whereDate('date',Carbon::parse(Carbon::today())->toDateString())->get();
            return response()->json([
                'status'=>true,
                'today'=>$check_today_,
                'yesterday'=>$day->isMonday() ? $check_saturday : $check_yesterday_
            ],200);


        }else{

            $this->update_daily_summary($store);
            $check_today_ = DailySummary::where('store',$store)->whereDate('date',Carbon::parse(Carbon::today())->toDateString())->get()->first();

            $date = Carbon::now();
            $day = Carbon::parse($date);

            $date_ = new DateTime($date->toDateString());
            $date_->modify("-1 day");
            $date_->format("Y-m-d");

            $check_yesterday_ = DailySummary::where('store',$store)->whereDate('date',Carbon::parse(Carbon::yesterday())->toDateString())->get()->first();
            $check_saturday = DailySummary::where('store',$store)->whereDate('date',$date_)->get()->first();



            return response()->json([
                'status'=>true,
                'today'=>$check_today_,
                'yesterday'=>$day->isMonday() ? $check_saturday : $check_yesterday_
            ],200);

        }

    }catch (\Throwable $e){
        return response()->json([
            'status'=>false,
            'message'=>$e->getMessage(),
            'trace'=>$e->getTrace()

        ],500);
    }


}


}
