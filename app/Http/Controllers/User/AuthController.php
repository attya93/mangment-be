<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use App\User;
use JWTAuth;
use Validator;
use Illuminate\Support\Facades\Hash;
use Tymon\JWTAuth\Exceptions\JWTException;


class AuthController extends Controller
{
    //
    protected $user;
    public function __construct(){
          $this->user = new User;
     }

    public function register(Request $requset){
         $validator = validator::make($requset->all(),[
            "fName"=>"required|string",
            "lName"=>"required|string",
            "email"=>"required|email",
            "password"=>'required|string|min:6'
         ]);

         if($validator->fails()){
            return response()->json([
                "success"=>false,
                "message"=>$validator->messages()->toArray()
            ],400);
         }
         $check_email =$this->user->where('email',$requset->email)->count(); 
         if($check_email >0){
             return response()->json([
                "success"=>false,
                "message"=>"this email already exist please try another email"
             ],401);
         }
         $registerComplete = $this->user::create([
             'fName'=>$requset->fName,
             "lName"=>$requset->lName,
             "email"=>$requset->email,
             "password"=>Hash::make($requset->password)
         ]);
        if($registerComplete){
            return $this->login($requset);
        }
       
     }

    public function login(Request $requset){
        $validator = validator::make($requset->only('email','password'),[
            "email"=>"required|email",
            "password"=>'required|string|min:6'
        ]);

        if($validator->fails()){
            return response()->json([
                "success"=>false,
                "message"=>$validator->messages()->toArray()
            ],400);
         }
         
        $input = $requset->only('email','password');
        $jwtToken = auth('users')->attempt($input);
        if(!$jwtToken ){
            return response()->json([
                "success"=>false,
                "message"=>"Invald Email or Password"
            ],400);
        }
        return response()->json([
            "success"=>true,
            "token"=>$jwtToken
        ]);
    }
}