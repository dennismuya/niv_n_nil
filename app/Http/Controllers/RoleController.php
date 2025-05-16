<?php

namespace App\Http\Controllers;

use App\Models\Role;
use Illuminate\Http\Request;

class RoleController extends Controller
{
    //
    public function get_roles(){


        try {
            $roles = Role::where('id','!=',1 )->get();
            return response()->json([
                'status'=>true,
                'roles'=>$roles
            ]);
        }
        catch (\Throwable $e){
            return response()->json([
                'status'=>false,
                'roles'=>$e
            ]);

        }







    }
}
