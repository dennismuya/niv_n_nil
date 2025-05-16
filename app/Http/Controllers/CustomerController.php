<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use App\Models\CustomerInvoice;
use App\Models\Invoice;
use App\Models\InvoicePayment;
use App\Models\InvoiceProduct;
use App\Models\Product;
use App\Models\Store;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use stdClass;

class CustomerController extends Controller
{
    public function add_customer(Request $request,$store=null)
    {
        $validateUser = Validator::make($request->all(),
            [
                'customer' => 'required'
            ]);

        if ($validateUser->fails()) {
            return response()->json([
                'status' => false,
                'message' => 'validation error',
                'errors' => $validateUser->errors()
            ], 401);
        }

        try {
            $store_ = Store::find($store);
            $customer = Customer::create([
                'name' => $request->customer,
                'store' =>$store_->id,
                'phone1' => $request->phone1,
                'phone2' => $request->phone2,
                'phone3' => $request->phone3

            ]);

            return response()->json([
                'status' => true,
                'message' => 'customer added succesfully',
                'data' => $customer
            ], 200);


        } catch (\Throwable $e) {
            return response()->json([
                'status' => false,
                'message' => 'customer not added',
                'details' => $e->getMessage()
            ], 500);
        }


    }

    public function edit_customer(Request $request, $id = null)
    {
        try {

            Customer::where('id', $id)->update(['name'=>$request->customer,'phone1'=>$request->phone1]);
            return response()->json([
                'status' => 'update successful'
            ]);
        } catch (\Throwable $e) {
            return response()->json([
                'status' => 'An error occurred',
                'error' => $e->getMessage()
            ]);

        }


    }

    public function get_customers($store=null){

        try {
            $customers = Customer::where('store',$store)->get();

            return response()->json([
                'status'=>true,
                'data'=>$customers

            ]);

        }
        catch (\Throwable $e){
            return response()->json([
                'status'=>false,
                'message'=>$e->getMessage()

            ]);
        }



    }
    public function get_customers_with_invoice($store=null){

        try {
            $customers = Customer::has()->where('store',$store)->get();

            return response()->json([
                'status'=>true,
                'data'=>$customers

            ]);

        }
        catch (\Throwable $e){
            return response()->json([
                'status'=>false,
                'message'=>$e->getMessage()

            ]);
        }



    }
    public function  customer_add_invoice(Request $request){
        $customer = $request->customer;
        $invoice = $request->invoice;


       $_invoice =  CustomerInvoice::create([
           'customer'=>$customer,
           'invoice'=>$invoice

        ]);
       return response()->json([
           'status'=>true,
           'data'=>$_invoice
       ]);


    }
    public function get_customers_with_invoices($store=null){


        try {
            $data = Customer::whereHas('invoices')->where('store',$store)->with('invoices')->get();


            $customers_with_pedding = [];



            foreach ($data as $customer){
                $all_invoices = 0;


                $invoices = Invoice::where('customer', $customer['id'])->with('invoice_with_unreturned_stock')->get();

                $balance_after = InvoicePayment::where('customer', $customer['id'])->sum('total_payment');


                foreach ($invoices as $invoice) {
                    $invoice_total = 0;




                    foreach ($invoice->invoice_with_unreturned_stock as $invoice_stock_) {
                        $invoice_total += $invoice_stock_['price'];
                    }


                    $all_invoices += $invoice_total;



                    Invoice::where('id', $invoice->id)->update([
                        'invoice_total' => $invoice_total
                    ]);

                }


//                    here


                $customer->invoice_summary = (integer)$all_invoices;
                $customer->made_payments = (integer)$balance_after;
                $customer->pending_balance =(integer)$all_invoices - $balance_after ;
                    array_push($customers_with_pedding,$customer);
            }


            return response()->json([
                'data'=>$customers_with_pedding
            ],200);

        }
        catch (\Throwable $exception){
            return response()->json([
                'data'=>$exception->getMessage()
            ],500);

        }

    }
    public function get_suppliers($store=null){

        try {
            $customers= Customer::whereHas('supplies')->where('store',$store)->with('supplies')->get();

            foreach ($customers as $customer) {
                $total = 0;
                foreach ($customer->supplies as $supply) {
                    $total += $supply->buying_price;
                }
                $customer->total_supply_amount = $total;
                $customer->no_of_supplies = count($customer->supplies);
                unset($customer->supplies);
            }

            return response()->json([
                'status'=>true,
                'data'=>$customers

            ]);

        }catch (\Throwable $e){

            return response()->json([
                'status'=>false,
                'error'=>$e->getMessage()

            ]);
        }





    }

    public function add_multiple_customer(Request $request,$store=null)
    {
        try {
            $store_ = Store::find($store);
            $contacts =$request->contacts;
            foreach ($contacts as $contact){

                Customer::create([
                    'name' => $contact['Name'],
                    'store' =>$store_->id,
                    'phone_number' => $contact['Phone'],

                ]);
            }


            return response()->json([
                'status' => true,
                'message' => 'customers added succesfully',

            ], 200);


        } catch (\Throwable $e) {
            return response()->json([
                'status' => false,
                'message' => 'customer not added',
                'details' => $e->getMessage()
            ], 500);
        }


    }



}
