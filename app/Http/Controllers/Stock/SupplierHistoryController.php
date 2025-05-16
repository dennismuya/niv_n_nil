<?php

namespace App\Http\Controllers\Stock;

use App\Http\Controllers\Controller;
use App\Models\Customer;

use App\Models\SupplyPayment;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class SupplierHistoryController extends Controller
{
    //

    public function get_suppliers($store)
    {

        try
        {
            $suppliers = Customer::where('store',$store)->whereHas('supplies')->get();

            foreach ($suppliers as $supplier){
               $supply_total = DB::table('supplier_histories')->where('supplier',$supplier->id)->sum('total_price');
                $supplier->amount_supplied = (integer)$supply_total;
            }

            return response()->json([
                'status' => true,
                'supplies' => $suppliers
            ], 200);


        } catch (\Throwable $exception)
        {
            return response()->json([
                'status' => false,
                'messsage' => 'an error occured',
                'line'=>$exception->getLine(),
                'e' => $exception->getMessage(),
                'tr'=>$exception->getTrace()
            ], 500);
        }

    }

    public function get_customer_supplies($supplier){
       try {
            $supply_total = DB::table('stock_histories')
                ->where('supplier', $supplier)
                ->sum('buying_price');



            $supplies  = DB::table('supplier_histories')
                ->where('supplier', $supplier)
                ->join('new_stocks','supplier_histories.stock','=','new_stocks.id')
                ->select('supplier_histories.*','new_stocks.*')
                ->get();







            $new_supp = $supplies;


            $new_supplies = collect($new_supp);

            $extra_ = $new_supplies->groupBy('supply_date');


            return response()->json([
                'status' => true,
                'supplies' => $extra_,
                'supply_total'=> (int)$supply_total
            ]);
        }

        catch (\Throwable $exception){
           return response()->json([
                'status'=>false,
               'e'=>$exception->getMessage(),
               'tr'=>$exception->getTrace()

           ],500);
        }
    }


    public function add_supply($supplier){





    }

    public function pay_supplier(Request $request,$store,$supplier){

        try
        {
            SupplyPayment::create([
                'customer'=>$supplier,
                'amount'=>$request->amount,
                'date'=>$request->payment_date ?: Carbon::parse(Carbon::now()),
                'mpesa'=>$request->mpesa,
                'cash'=>$request->cash,
                'expenses'=>$request->expenses,
                'user'=>Auth::id(),
                'store'=>$store,
            ]);

            return  response()->json([
                'status'=>true,
                'message'=>'payment done successfully'
            ]);

        }catch(\Throwable $exception){
            return response()->json([
                'status'=>true,
                'message'=>$exception->getMessage(),
                'error'=>$exception->getTrace()

            ],500);

        }

    }





}
