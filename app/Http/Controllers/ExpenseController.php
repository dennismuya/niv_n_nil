<?php

namespace App\Http\Controllers;

use App\Models\DailySummary;
use App\Models\Expense;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class ExpenseController extends MyBaseController
{
    //
    public function add_expense(Request $request, $store = null)
    {
        $validateUser = Validator::make($request->all(),
            [
                'amount' => 'required',

            ]);

        if ($validateUser->fails())
        {
            return response()->json([
                'status' => false,
                'message' => 'validation error',
                'errors' => $validateUser->errors()
            ], 401);
        }

        try
        {

            $amount = (integer)$request->amount;

            $expense = Expense::create([
                'user' => Auth::id(),
                'created_by' => Auth::id(),
                'amount' => $amount,
                'reason' => $request->reason,
                'store' => $store,
                'date' => Carbon::parse(Carbon::now())->toDateString(),
                'time' => Carbon::parse(Carbon::now())->toTimeString(),
            ]);


            $recent_id = $this->get_recent_summary_id($store);

            DailySummary::find($recent_id)->increment('expenses', $amount);
            DailySummary::find($recent_id)->decrement('closing_balance', $amount);


            return response()->json([
                'status' => true,
                'data' => $expense
            ]);

        } catch (\Throwable $e)
        {
            return response()->json([
                'status' => false,
                'data' => $e->getMessage()
            ]);

        }


    }

    public function get_expenses($store = null)
    {

        try
        {
            $expenses = Expense::with('user')->orderBy('created_at', 'desc')->where('store', $store)->get();


            foreach ($expenses as $expense)
            {
                $user_name = User::where('id', $expense['created_by'])->get('user_name')->first();
                $new_date = Carbon::parse($expense['created_at']);
                $expense->created_by = $user_name->user_name;

                $expense['_at'] = $new_date->toTimeString();
                $expense['date'] = $new_date->toDateString();

            }

            $collect = collect($expenses);
            $grouped = $collect->groupBy('date');
            return response()->json([
                'status' => true,
                'data' => $grouped
            ]);


        } catch (\Throwable $e)
        {
            return response()->json([
                'status' => false,
                'message' => "could not get expenses "
            ]);

        }

    }

    public function add_date_time_to_expenses()
    {

        try
        {

            $expenses = Expense::all();

            foreach ($expenses as $expense)
            {
                $expense->date = Carbon::parse($expense->created_at)->toDateString();
                $expense->time = Carbon::parse($expense->created_at)->toTimeString();
                $expense->save();
            }


            return response()->json([
                'status' => true,
                'message' => 'expenses updated successfully',
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
}





