<?php

namespace App\Http\Controllers;

use App\Models\Bank;
use Illuminate\Http\Request;

class BankController extends Controller
{
    //

    public function get_banks()
    {
        try {
            $banks = Bank::all();
            return response()->json([
                'status' => true,
                'banks' => $banks

            ]);

        } catch (\Throwable $e) {
            return response()->json([
                'status' => false,
            ]);

        }


    }
}
