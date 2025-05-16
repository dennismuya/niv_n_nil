<?php

namespace App\Http\Controllers;

use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use PHPUnit\Event\Code\Throwable;

class ProductController extends Controller
{
    /**
     * Add Product
     */

    public function add_product(Request $request)
    {
        $validateUser = Validator::make($request->all(),
            [
                'name' => 'required|unique:products,name',
                'category' => 'required'
            ]);

        if ($validateUser->fails()) {
            return response()->json([
                'status' => false,
                'message' => 'validation error',
                'errors' => $validateUser->errors()
            ], 401);
        }

        $product = Product::create([
            'SKU' => $request->barcode,
            'name' => $request->name,
            'category' => $request->category,
        ]);

        return response()->json([
            'status' => true,
            'message' => 'Product ' . $product->name . ' registered successfully',
            'data' => $product
        ]);


    }

//    get all
    public function get_all()
    {

        $product = Product::all();
        return response()->json([
            'status' => true,
            'message' => '',
            'data' => $product

        ]);


    }

    public function update_product(Request $request,$id=null){

        try {
            $product = Product::find($id);
            if(!$product){
                return response()->json([
                    'status'=>false,
                    'message'=>'product not found'

                ],500);



            }


            $product->name  = $request->name;
            $product->category = $request->category;
            $product->SKU = $request->SKU;

            $product->save();

            return response()->json([
                'status'=>true,
                'message'=>'product '.$product->name.' updated successfully'

            ]);
        }
        catch (\Throwable $e){
            return response()->json([
                'status'=>false,
                'message'=>$e->getMessage()
            ]);


        }




    }

    public function delete_product(Request $request, $id = null)
    {

        try {
            $product = Product::find($id);
            if (!$product) {
                return response()->json([
                    'status' => false,
                    'error' => "Product Not Found"
                ], 404);
            }
            $product->delete();
            return response()->json([
                'status' => true,
                'message' => "product deleted successfully"
            ]);

        } catch (\Throwable $e) {
            return response()->json([
                'status' => false,
                'error' => $e->getMessage()
            ], 500);

        }


    }


}

