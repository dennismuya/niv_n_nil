<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use App\Models\DailySummary;
use App\Models\Deposit;
use App\Models\Expense;
use App\Models\Invoice;
use App\Models\InvoicePayment;
use App\Models\InvoiceProduct;
use App\Models\Sale;
use App\Models\SaleProduct;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use stdClass;

class DailyAnalytics extends MyBaseController
{


    public function create_initial_analytics($store,$date)
    {
        $opening_balance = $this->get_opening_balance($store,$date);
        return DB::table('daily_summaries')
            ->updateOrInsert([
                'date' => Carbon::today(),
                'store' => $store,
            ],
                [
                    'opening_balance' => $opening_balance,
                    'opening_time' => Carbon::parse(Carbon::now())->toDateTime(),
                    'closing_balance' => $opening_balance,
                ]
            );

    }

    public function get_daily_analytics($store)
    {
        try
        {
            $date = Carbon::parse(Carbon::now())->toDateString();

            $daily = $this->get_day_analytics_by_date($store,$date);

            if (!$daily)
            {
                $this->create_initial_analytics($store,$date);
            }

            $this->refresh_dairy_summary($store,$date);

            return response()->json([
                'status' => true,
                'message' => 'hello Dolly',
                'date'=>Carbon::parse(Carbon::now())->isoFormat('dddd, MMMM D, YYYY'),
                'daily' =>  $this->get_day_analytics_by_date($store,$date),
                'last_day' => $this->get_last_day_analytics($store,$date)
            ]);



        } catch (\Throwable $e)
        {
            return response()->json([
                'status' => true,
                'message' => $e->getMessage(),
                'error' => $e->getTrace()
            ],500);

        }


    }


}
