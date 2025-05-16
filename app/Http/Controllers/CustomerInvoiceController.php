<?php

namespace App\Http\Controllers;

use App\Models\InvoicePayment;
use Carbon\Carbon;
use Illuminate\Http\Request;

class CustomerInvoiceController extends Controller
{
    //

//    create invoice payment dates

public function  create_invoice_pay_date(){

    try {
        $all_payments = InvoicePayment::all();

        foreach ($all_payments as $all_payment){
            $newdate = Carbon::parse($all_payment->created_at);
            $all_payment->date = $newdate->toDateString();

            $all_payment->save();

        }
        return response()->json([
            'status'=>true,
            'me'=>"done"
        ],200);


    }
    catch (\Throwable $e){
        return response()->json([
            'status'=>false,
            'e'=>$e->getMessage()
        ],500);

    }







}




}
