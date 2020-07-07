<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use App\Contacts;
use Validator;
use Illuminate\Routin\UrlGenerator;

class contactController extends Controller
{
    //
    protected $contacts;
    protected $baseUrl;

    public function __construct(UrlGenerator $url){
        $this->middleware("auth:users");
        $this->contacts = new Contacts;
        $this->baseUrl = $url->to('/');
    }

    /**
     * this function is to addContect endPoint
     * receives token [fName,phone]
     */
    public function addContacts(Request $req){
         $validator = Validator::make($req ->all(),[
             "token"=>"required",
             "fName"=>"required|string",
             "phone"=>"required|string"
         ]);

         if($validator->fails()){
            return response()->json([
                "success"=>false,
                "message"=>$validator->messages()->toArray()
            ],500);
         }

         $profilePicture = $req->userImg;
         $fileName ="";

         //check if user upload an imge to server
         //if not use set the default image
         if($profilePicture === null){
            $fileName ="default-avater.png";
         }else{
             // genterate name to image uploaded
             $generateName = uniqid()."_".time().date('ymd')."IMG";
             $base64Image = $profilePicture;
             //check fro type of image 
             $fileBin = file_get_contents($base64Image);
             $mineType =mime_content_type( $fileBin );
             switch($mineType){
                 case"image/png":
                    $fileName = $generateName .".png";
                    break;
                case"image/jpeg":
                    $fileName = $generateName .".jpeg";
                    break;
                case"image/jpg":
                    $fileName = $generateName .".jpg";
                    break;
                default:
                return response()->json([
                    "success"=>false,
                    "message"=>"The Image must be png , jpg or jpeg"
                ],500);
             }
         }
         // extract token from req
         $userToken = $req->token;
         //find the user that has the token
         $user = auth("users")->authenticate($userToken);  
         //get id from the user we found
         $userId = $user->id;
         $this->contacts->user_id =$userId;
         $this->contacts->phone = $req->phone;
         $this->contacts->fName = $req->FName;
         $this->contacts->lName = $req->lName;
         $this->contacts->email = $req->email;
         $this->contacts->image = $fileName;
         $this->contacts->save();
         file_put_contents("./profile_image/".$fileName,$fileBin);

         return response()->json([
             "success"=>true,
             "message"=>"Add user Success"
         ],200);
    }

     /**
     * get contact to specific user
     * receives token [fName,phone]
     */
    public function getPaginatedData($pagnation =null , $token){
        $fileDirectory = $this->baseUrl."/profile_image";
        $user = auth("users")->authenticate($token);
        $userId = $user->user_id;
        if($pagnation ===null ||$pagnation ===""){
            $contacts = $this->contacts->where("user_id",$userId)->orderBy('id','DESC')
            ->get()->toArray();
            
            return response()->tojson([
                "success"=>true,
                "data"=>$contacts,
                "file_directory"=>$fileDirectory
            ],200);
        }
        $contactsPagni = $this->contacts->where("user_id",$userId)->orderBy('id','DESC')
        ->paginate($pagnation)->toArray();

        return response()->tojson([
            "success"=>true,
            "data"=>$contactsPagni,
            "file_directory"=>$fileDirectory
        ],200);
    }

    /**
     * update contact 
     * receives token [fName,phone]
     */
} 