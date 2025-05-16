<?php

namespace App\Http\Controllers;

use App\Models\SupplyPayment;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class SupplyPaymentController extends Controller
{
    //pay supplier

    public function pay_supplier(Request $request, $store = null, $supplier = null)
    {
        try {
            SupplyPayment::create([
                'customer' => $supplier,
                'store' => $store,
                'user' => Auth::id(),
                'amount' => $request->total_amount,
                'mpesa' => $request->mpesa,
                'cash' => $request->cash,
                'date' => Carbon::parse(Carbon::now())->toDateString(),

            ]);

            return response()->json([
                'status' => true,
                'message' => 'supplier payment successful',


            ], 200);


        } catch (\Throwable $e) {
            return response()->json([
                'status' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }

}
