<?php

namespace App\Http\Controllers\Sale;

use App\Http\Controllers\Controller;
use App\Models\Sale;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class NewSaleStockController extends Controller
{
    //

    public function add_store_and_date_to_sale_stock()
    {

        try

        {
            DB::table('new_sale_stocks')
                ->where('store', null)
                ->chunkById(500,
                    function (Collection $stocks) {
                        foreach ($stocks as $stock)
                        {
                            $sale = Sale::find($stock->sale);

                            DB::table('new_sale_stocks')->where('id', $stock->id)->update([
                                'store' => $sale->store,
                                'date' => $sale->date
                            ]);
                        }
                    });
            return response()->json([
                'status' => false,
                'message' => "done successfully",

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

