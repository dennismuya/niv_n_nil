<?php

namespace App\Http\Controllers;

use App\Models\Bank;
use App\Models\DailySummary;
use App\Models\Deposit;
use App\Models\Store;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class DepositController extends MyBaseController
{
    //

    public function make_deposit(Request $request,$store=null,$bank=null){
        try {

            $amount = $request->amount;

            $new_deposit =  Deposit::create([
                'user'=>Auth::id(),
                'amount'=>$amount,
                'bank'=>$bank,
                'store'=>$store,
                'date'=> Carbon::parse(Carbon::now())->toDateString()

            ]);

            $recent_id = $this->get_recent_summary_id($store);

            DailySummary::find($recent_id)->increment('bank_deposits', $amount);
            DailySummary::find($recent_id)->decrement('closing_balance', $amount);

            return response()->json([
                'status'=>true,
                'message'=>"deposit made successfully",
                'data'=>$new_deposit
            ],200);

        }catch (\Throwable $e){
            return response()->json([
                'status'=>true,
                'response'=> 'Deposit Failed',
                'error'=>$e->getMessage()
            ],500);
        }





    }

    public function get_deposits(Request $request,$store=null){

        try {
            $deposits = Deposit::where('store',$store)->orderBy('id', 'desc')->get();

            foreach ($deposits as $deposit){
                $bank = Bank::find($deposit->bank);
                $user = User::find($deposit->user);
                $deposit->bank_name = $bank->bank_name;
                $deposit->user_name = $user->user_name;
                $new_date = Carbon::parse($deposit->created_at);
                $deposit['time'] = $new_date->toTimeString();
                $deposit['date'] = $new_date->toDateString();
            }
            return response()->json([
                'status'=>true,
                'deposits'=>$deposits
            ]);
        }catch (\Throwable $e){
            return response()->json([
                'status'=>true,
                'message'=>$e->getMessage()
            ],500);

        }



    }

    public function add_date_to_deposits(){
        try {
            $deposits = Deposit::all();
            foreach ($deposits as $deposit) {
                $deposit->date = Carbon::parse($deposit->created_at)->toDateString();
                $deposit->save();
            }
            return response()->json([
                'status'=>true,
                'message'=>'added date to deposits'
            ]);


        }catch (\Throwable $e){
            return response()->json([
                'status'=>false,
                'message'=>$e->getMessage()
            ],500);


        }

    }
}
