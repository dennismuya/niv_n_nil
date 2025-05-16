<?php /** @noinspection ALL */

namespace App\Http\Controllers;

use App\Http\Resources\UserResource;
use App\Models\Bank;
use App\Models\Category;
use App\Models\Customer;
use App\Models\Product;
use App\Models\Role;
use App\Models\Store;
use App\Models\StoreUser;
use App\Models\User;
use App\Models\UserRole;
use Cassandra\Custom;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use TheSeer\Tokenizer\Exception;
use function NunoMaduro\Collision\Exceptions\getMessage;

class AuthController extends Controller
{
    /**
     * create_user
     * @param Request $request
     * @return User
     * */

    public function signup(Request $request)
    {
        try {
//            data validation
            $validateUser = Validator::make($request->all(),
                [

                    'user_name' => 'required|unique:users,user_name',

                    'password' => 'required'
                ]);

            if ($validateUser->fails()) {
                return response()->json([
                    'status' => false,
                    'message' => 'validation error',
                    'errors' => $validateUser->errors()
                ], 401);
            }


            $user = User::create([
                'first_name' => $request->first_name,
                'last_name' => $request->last_name,
                'email' => $request->email,
                'user_name' => $request->user_name,
                'password' => Hash::make($request->password),

            ]);


            return response()->json([
                'status' => true,
                'message' => 'user ' . $user->user_name . ' registered successfully',
                'data' => $user

            ]);

        } catch (\Throwable $th) {
            return response()->json([
                'status' => false,
                'message' => $th->getMessage()
            ], 500);
        }

    }

    public function register_user(Request $request, $store = null)
    {
        try {
//            data validation
            $validateUser = Validator::make($request->all(),
                [
                    'user_name' => 'required|unique:users,user_name',
                    'password' => 'required'
                ]);

            if ($validateUser->fails()) {
                return response()->json([
                    'status' => false,
                    'message' => 'validation error',
                    'errors' => $validateUser->errors()
                ], 401);
            }


            $user = User::create([
                'user_name' => $request->user_name,
                'password' => Hash::make($request->password),
            ]);

            $role_ = UserRole::create([
                'user' => $user->id,
                'role' => $request->role

            ]);
            $user->shop()->attach($store);


            $user_with_store = User::with('shop')->where('id', $user->id)->first();


            return response()->json([
                'status' => true,
                'message' => 'user ' . $user->user_name . ' registered successfully',
                'data' => $user_with_store,
                'role' => $role_

            ]);

        } catch (\Throwable $th) {
            return response()->json([
                'status' => false,
                'message' => $th->getMessage()
            ], 500);
        }

    }

    /**
     * login
     * */
    public function login(Request $request)
    {
        try {
            $validateUser = Validator::make($request->all(),
                [
                    'user_name' => 'required',
                    'password' => 'required'
                ]);

            if ($validateUser->fails()) {
                return response()->json([
                    'status' => false,
                    'message' => 'validation error',
                    'errors' => $validateUser->errors()
                ], 401);
            }

            if (!Auth::attempt($request->only(['user_name', 'password']))) {

//                Log::info('Login Failed', [
//                    'name' => $request->user_name,
//                    'password' => $request->passsword
//                ]);

                Log::channel('slack')->info('Login Failed',[
                    'name' => $request->user_name,
                    'password' => $request->passsword
                ]);

                return response()->json([
                    'status' => false,
                    'message' => 'Incorrect  Username or Password',
                ], 401);
            }

            $user = User::where('user_name', $request->user_name)->with('shop')->first();
            $token = $user->createToken("API TOKEN")->plainTextToken;
//            $user->shop_ = $user->shop[0];


            $user->token = $token;

            $user->message_ = "Login Successful";
            $data = [
                "status" => true,
                "message" => $user->message_,
                "profile" => [
                    "user_id" => $user->id,
                    "user_name" => $user->user_name,

                ],
                "auth_token" => $user->token,
                "user"=>$user
            ];

            return response()->json(
                [
                    "data" => $data
                ]

            );


//            return new UserResource($user);

        } catch (\Throwable $th) {
            return response()->json([
                'status' => false,
                'message' => $th->getMessage()
            ], 500);
        }

    }

    public function get_users(Request $request, $user = null)
    {
        $users = User::where('id', '!=', 1)->with('role')->with('shop')->get();


        return response()->json([
            'status' => true,
            'users' => $users
        ]);

    }

    public function delete_user(Request $request, $id = null)
    {
        $user_id = $id;

        try {
            $delet_user = User::find($user_id);
            $delet_user->delete();
            return response()->json([
                'status' => true,
                'message' => 'user deleted successfully'
            ]);
        } catch (\Throwable $exception) {
            return response()->json([
                'status' => false,
                'message' => $exception->getMessage()
            ]);

        }


    }

    public function get_user($store=null)
    {
        $id = Auth::id();

        try {
            $user = User::with('shop')->with('role')->where('id', $id)->get();
            $banks = Bank::all();
            $stores = Store::all();
            $products= Product::all();

//            $customers = Customer::where('store',$user->shop)->get();

            $roles = Role::all();



            return response()->json([
                'status' => false,
                'user' => $user,
                'banks'=>$banks,
                'stores'=>$stores,
                'products'=>$products,

                'roles'=>$roles,


            ], 200);
        } catch (\Throwable $e) {
            return response()->json([
                'status' => false,
                'message' => $e->getMessage()
            ], 500);

        };


    }

    public function change_user_password(Request $request, $user_ = null)
    {
        $user = $user_;
        $password_ = $request->password;
        try {
            $new_user_password = User::find($user);
            if (!$new_user_password) {
                return response()->json([
                    'status' => false,
                    'message' => 'User incorrect or not found'
                ], 401);
            }

            $new_user_password->password = Hash::make($password_);

            $new_user_password->save();
            return response()->json([
                'status' => true,
                'message' => 'password changed successfully'
            ]);


        } catch (\Throwable $e) {
            return response()->json([
                'status' => false,
                'message' => 'User Change password failed'
            ]);

        }


    }




//    public function edit_user(Request $request)
//    {
//        $user_ = $request->edit_user;
//        try {
//           $user =User::find($user_->id)->first();
//           if(!$user){
//
//               return response()->json([
//                   'status'=>false,
//                   'message'=>'user not found'
//               ],500);
//
//
//           }
//           $user->user_name = $user_->user_name;
//           $user->
//
//
//
//        } catch (\Throwable $e) {
//            return  response()->json([
//                'status'=>false,
//                'message'=>$e->getMessage()
//
//            ]);
//
//        }
//
//
//    }


}
