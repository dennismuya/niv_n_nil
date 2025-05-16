<?php

namespace App\Http\Controllers;

use App\Models\NewInvoice;
use App\Models\Slip;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class SlipController extends Controller
{
    //
    public function move_old_slips(Request $request, $store = null, $customer = null)
    {
        try {

            $slips = $request->slips;

            foreach ($slips as $slip) {
                Slip::create([
                    'slipped_by' => Auth::id(),
                    'slip_date' => $slip['date'],
                    'slip_total' => $slip['slip_total'],
                    'store' => $store,
                    'customer' => $customer,
                ]);

                return response()->json([
                    'status' => false,
                    'message' => 'slips added successfully',
                ], 200);
            }


        } catch (\Throwable $exception) {
            return response()->json([
                'status' => false,
                'message' => $exception->getMessage(),
                'error' => $exception->getTrace(),


            ], 500);

        }
    }

    public function slip(Request $request, $store = null, $customer = null)
    {

        try {
            Slip::create([
                'slipped_by' => Auth::id(),
                'slip_date' => Carbon::parse(Carbon::today())->toDateString(),
                'slip_total' => $request->slip_total,
                'store' => $store,
                'customer' => $customer,
            ]);

            return response()->json([
                'status' => true,
                'message' => 'slip successful',

            ]);


        } catch (\Throwable $exception) {
            return response()->json([
                'status' => false,
                'message' => $exception->getMessage(),
                'error' => $exception->getTrace()
            ]);


        }


    }


    public function balance_debt($customer=null,$store=null){
        $new_invoices = NewInvoice::where('customer',$customer)->where('store',$store)->where('debt_balanced',false);
//        $customer_supplies = ;

    }



}
