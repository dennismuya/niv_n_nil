<?php

namespace App\Http\Controllers\Stock;

use App\Http\Controllers\Controller;
use App\Http\Controllers\MyBaseController;
use App\Models\Stock\TransferHistory;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use stdClass;

class TransferHistoryController extends MyBaseController
{
    //transfer stock
    public function new_stock_transfer(Request $request, $store = null, $column = null)
    {

        $history = new stdClass();
        $history->stock = null;
        $history->stock_action = null;
        $history->buying_price = null;
        $history->selling_price = null;
        $history->quantity = null;
        $history->supplier = null;
        $history->user = null;
        $history->store = null;
        $history->previous_stock = null;
        $history->stock_after = null;
        $history->reason = null;
        $history->serial_number = null;
        $history->serial_array = null;
        $history->returned_from = null;
        $history->replaced = null;
        $history->sale_replaced = null;

        try
        {

            $stocks_ = $request->stock_to_transfer;
            $to_store = $request->to_store;

            foreach ($stocks_ as $stock_)
            {
                TransferHistory::create([
                    'stock' => $stock_['id'],
                    'origin_store' => $store,
                    'destination_store' => $to_store,
                    'transfer_date' => Carbon::parse(Carbon::now())->toDateString(),
                    'transferred_quantity' => $stock_['transfer_quantity'],
                    'transferred_by' => Auth::id(),
                ]);
                $stock__ = $this->get_single_stock($column, $stock_['id']);
                $this->deduct_stock($stock_['id'], $column, $stock_['transfer_quantity']);

                $history->stock = $stock_['id'];
                $history->action = 5;
                $history->quantity = $stock_['transfer_quantity'];
                $history->store = $store;
                $history->previous_stock = $stock__->stock_quantity;
                $history->stock_after = $stock__->stock_quantity - $stock_['transfer_quantity'];
                $this->make_stock_history($history);


            }

            return response()->json([
                'status' => false,
                'message' => 'stock transferred successfully',
                'stock' => $this->get_stock($column)
            ], 200);


        } catch (\Throwable $e)
        {

            return response()->json([
                'status' => false,
                'message' => $e->getMessage(),
                'error' => $e->getTrace()

            ], 500);
        }


    }

//    get received stock

    public function get_received_new_stocks($store)
    {
        try
        {
            $receive_history = TransferHistory::with('stock')
                ->where('destination_store', $store)
                ->where('received_quantity', false)
                ->orWhere('pedding_quantity', true)
                ->get();

            return response()->json([
                'status' => true,
                'received_stock' => $receive_history,
            ], 200);

        } catch (\Throwable $e)
        {
            return response()->json([
                'status' => false,
                'message' => 'error retrieving received stock',
                'e.message' => $e->getMessage(),
                'e' => $e->getTrace()
            ], 500);


        }

    }

    public function receive_new_stock(Request $request, $store, $column)
    {
        try
        {
            $received_quantity = $request->quantity;
            $stock_id = $request->stock_id;
            $transfer_id = $request->transfer_id;

            $history = new stdClass();
            $history->stock = null;
            $history->stock_action = null;
            $history->buying_price = null;
            $history->selling_price = null;
            $history->quantity = null;
            $history->supplier = null;
            $history->user = null;
            $history->store = null;
            $history->previous_stock = null;
            $history->stock_after = null;
            $history->reason = null;
            $history->serial_number = null;
            $history->serial_array = null;
            $history->returned_from = null;
            $history->replaced = null;
            $history->sale_replaced = null;

            TransferHistory::where('stock', $stock_id)->where('destination_store', $store)->where('id', $transfer_id)
                ->update([
                    'received_by' => Auth::id(),
                    'received_quantity' => $received_quantity,
                ]);
            $stock__ = $this->get_single_stock($column, $stock_id);
            $this->increase_stock($stock_id, $column, $received_quantity);

            $history->action = 10;
            $history->stock = $stock_id;
            $history->quantity = $received_quantity;
            $history->previous_stock = $stock__->stock_quantity;
            $history->stock_after = $stock__->stock_quantity + $received_quantity;
            $history->store = $store;


            $this->make_stock_history($history);

            return response()->json([
                'status' => false,
                'message' => 'stock received successfully',
                'stock' => $this->get_stock($column)
            ], 200);

        } catch (\Throwable $e)
        {
            return response()->json([
                'status' => false,
                'message' => $e->getMessage(),
                'error' => $e->getTrace()

            ], 500);
        }
    }


}

