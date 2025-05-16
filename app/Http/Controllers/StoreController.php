<?php

namespace App\Http\Controllers;

use App\Models\Store;
use App\Models\StoreUser;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use PHPUnit\Event\Code\Throwable;

class StoreController extends Controller
{
    /**
     * add store
     */

    public function add_store(Request $request)
    {
        $validateUser = Validator::make($request->all(),
            [
                'store_name' => 'required',
                'name' => 'required|unique:stores,name'
            ]);

        if ($validateUser->fails())
        {
            return response()->json([
                'status' => false,
                'message' => 'validation error',
                'errors' => $validateUser->errors()
            ], 401);
        }
        $store = Store::create([
            'name' => $request->name,
            'store_name' => $request->store_name,
            'location' => $request->location,
            'building' => $request->building,
            'primary_phone' => $request->primary_phone,
            'secondary_phone' => $request->secondary_phone,
            'website' => $request->website,
            'tagline' => $request->tagline,
            'products' => $request->products

        ]);
        return response()->json([
            'status' => true,
            'message' => 'store ' . $store->name . ' registered successfully',
            'data' => $store
        ]);
    }

    public function add_user_store(Request $request, $store_id, $user_id)
    {
        $store = $store_id;
        $user = $user_id;
        $message = "user found";

        try
        {
            if (StoreUser::where('shop', $store)->where('user', $user)->exists())
            {
                return response()->json([
                    'status' => false,
                    'message' => 'sorry the user already exists on the shop'
                ]);
            }

            $new_store_user = StoreUser::create([
                'user' => $user,
                'shop' => $store
            ]);


            return response()->json([
                'status' => true,
                'message' => 'user added successfully',
                'new_user' => $new_store_user
//                'new_user'=> $user_id,
//                'store'=> $store_id

            ]);
        } catch (\Throwable $exception)
        {
            return response()->json([
                'status' => false,
                'message' => $exception->getMessage()
            ]);

        }


    }


    public function get_stores()
    {

        try
        {
            $stores = Store::all();
            return response()->json([
                'status' => true,
                'data' => $stores

            ]);
        } catch (\Throwable $e)
        {
            return response()->json([
                'status' => false,
                'data' => $e->getMessage()

            ]);

        }

    }

    public function get_store_users($store = null)
    {
        try
        {
            $store_ = Store::find($store)->first();
            $store_users = $store_->store_users()->get()->toArray();

            return response()->json([
                'status' => true,
                'store_users' => $store_users
            ], 200);
        } catch (\Throwable $e)
        {

            return response()->json([
                'status' => false,
                'store' => $e->getMessage()
            ], 500);

        }

    }


    public function add_store_stock_column()
    {

        try
        {

            DB::table('stores')->where('id', 1)->update([
                'stock_name' => 'chini_ya_mnazi_quantity'

            ]);
            DB::table('stores')->where('id', 2)->update([
                'stock_name' => 'old_nation_quantity'

            ]);
            DB::table('stores')->where('id', 3)->update([
                'stock_name' => 'chini_ya_mnazi_quantity'

            ]);

            return response()->json([
                'status' => true,
                'message' => "success",

            ], 200);

        } catch (\Throwable $e)
        {

            return response()->json([
                'status' => false,
                'message' => $e->getMessage(),
                'error' => $e->getTrace()
            ], 500);
        }
        $stores = Store::all();

    }


}
