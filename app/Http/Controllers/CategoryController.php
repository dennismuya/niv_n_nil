<?php

namespace App\Http\Controllers;

use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class CategoryController extends Controller
{
    //

    public function get_categories(){
        try {
            $categories = Category::all();
            return response()->json([
                'status'=>true,
                'data'=>$categories

            ]);

        }

        catch (\Throwable $e){
            return response()->json([
                'status'=>false,
                'message'=>$e->getMessage()

            ],500);

        }




    }
    public function add_category(Request $request){
        $validateUser = Validator::make($request->all(),
            [
                'category' => 'required|unique:categories'
            ]);
        if($validateUser->fails()){
            return response()->json([
                'status' => false,
                'message' => "Input already exists or missing. Check your input",

            ], 500);
        }

        try {
            $category = Category::create([
                'category'=>$request->category
            ]);

            return response()->json([
                'status' => true,
                'message' => 'Category Added Successfully',
                'data'=>$category
            ]);

        }
        catch (\Throwable $e){
            return response()->json([
                'status' => false,
                'message' => $e->getMessage(),

            ], 500);

        }







    }
}
