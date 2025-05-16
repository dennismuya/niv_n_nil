<?php

namespace App\Http\Controllers\Cashflow;

use App\Http\Controllers\Controller;
use App\Http\Controllers\MyBaseController;
use App\Models\DailySummary;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use stdClass;

class NewCashFlowController extends MyBaseController
{

    public function get_month_cashflow($store,$year, $month)
    {

        try
        {
            $dates__ = [];

            $days = Carbon::now()->month($month)->daysInMonth;

            $this_month = Carbon::now()->month;

            if ($month == $this_month)
            {
                $days = Carbon::now()->day;
            }
            for ($i = 1; $i <= $days; $i++)
            {
                $dt = Carbon::create(Carbon::now()->year, month: $month, day: $i);


                if ($dt->dayOfWeek != Carbon::SUNDAY)
                {
                    array_push($dates__, $dt->toDateString());
                }


            }


            rsort($dates__);

            $working_dates = $dates__;
            $dates_of_the_month = [];
            foreach ($working_dates as $working_date)
            {

                $working_date_ = new stdClass();
                $working_date_->$working_date = new stdClass();
                $working_date_->$working_date->sale_stocks = $this->daily_sold_items_summary($working_date, $store);
                $working_date_->$working_date->summary = $this->get_day_analytics_by_date($store, $working_date);
                $working_date_->$working_date->debt_items = $this->daily_debt_items($store, $working_date);
                $working_date_->$working_date->debt_payments = $this->daily_debt_payments($store, $working_date);
                $working_date_->$working_date->expenses = $this->daily_expenses($store, $working_date);


                $dates_of_the_month[$working_date] = $working_date_->$working_date;
            }

            return response()->json([
                'status' => true,
                'cashflow' => $dates_of_the_month
            ]);
        } catch (\Throwable $e)
        {
            return response()->json([
                'status' => false,
                'message' => $e->getMessage(),
                'error' => $e->getTrace()
            ]);
        }


    }


    public function get_all_daily_cashflow($store,$year=null,$month=null)
    {

        try
        {
            $date = Carbon::now();
            $month_ = $month;
            $year_ = $year;

            if(!$year && !$month){
                $month_ = $date->month;
                $year_ = $date->year;
            }









            $all = DB::table('daily_summaries')
                ->where('store', $store)
                ->whereYear('date',$year_)
                ->whereMonth('date', '=', $month_)
                ->orderBy('date', 'desc')
                ->get();
            return response()->json([
                'status' => true,
                'daily' => $all,
                'month'=>$month_,
                'year'=>$year_
            ]);


        } catch (\Throwable $exception)
        {
            return response()->json([
                'status' => false,
                'message' => $exception->getMessage(),
                'error' => $exception->getTrace()

            ]);

        }


    }


    public function refresh__daily_by_date(Request $request,$store){
        try

        {
            $date  = $request->date;

           $data = $this->refresh_dairy_summary($store,$date);

            return response()->json([
                'status'=>true,
                'message' =>'refreshed successfully',
                'data'=>$data

            ]);

        }
        catch (\Throwable $exception){

            return response()->json([
                'status'=>false,
                'message'=>$exception->getMessage(),
                'error'=>$exception->getTrace()

            ]);

        }

    }


}
