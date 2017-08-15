<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use DateTime;
use App\User;
use App\Event;
use App\Usertoevent;
use Response;
use Purifier;
use Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use Auth;
use JWTAuth;
use Tymon\JWTAuth\Exceptions\JWTException;
use Symfony\Component\Process\Process;
use Symfony\Component\Process\Exception\ProcessFailedException;

class UserController extends Controller {

    /**
     * Apply jwt middleware to specific routes.
     *
     * @param  void
     * @return void
     */
    public function __construct() {
        $this->middleware('jwt.auth', ['only'=> [
            'userProfile',
            'join',		
            'destroy',
        ]]);
    } 

    /**
     * Persist user to database after sign up.
     *
     * @param Illuminate\Support\Facades\Request::class

     * @return  Illuminate\Support\Facades\Response::class
     */
    public function store(Request $request) {

	// CONSTANTS
     	$ADMIN_KEY = config('services.admin.key'); 

        // GET FORM INPUT
        $username = $request->input('username'); 
        $email = $request->input('email'); 
        $password = $request->input('password'); 

	// CHECK FOR FILE UPLOAD ERROR
        if ($_FILES['avatar']['error'] !== UPLOAD_ERR_OK) 
            die("Upload failed with error code " . $_FILES['avatar']['error']);
       
      	
	// PROVIDE CHECKS FOR VALID IMAGE UPLOAD
        $info = getimagesize($_FILES['avatar']['tmp_name']);
	
        if ($info === FALSE) 
            die("Unable to determine image type of uploaded file");
        
        if (($info[2] !== IMAGETYPE_GIF) && ($info[2] !== IMAGETYPE_JPEG) && ($info[2] !== IMAGETYPE_PNG)) 
            die("Not a gif/jpeg/png");

	// GET PROFILE IMAGE INPUT
        $avatar = $request->file('avatar');

        // VALIDATION RULES
        $rules = [
            'username' => 'required',
            'password' => 'required',
            'email' => 'required'
        ];

        // VALIDATE INPUT
        $validator = Validator::make(Purifier::clean($request->all()), $rules);

        if ($validator->fails()) 
            return Response::json(['error' => 'You must fill out all fields.']);

        // ENSURE UNIQUE USERNAME AND EMAIL
        $check = User::where('email', $email)->orWhere('name', $username)->first();

        if (!empty($check))
            return Response::json(['error' => 'Email or username already in use']);

	// STORE USER
        $user = new User;
        $user->name = $username;
        $user->email = $email;
        $user->password = Hash::make($password);

	// CHECK USER PRIVLAGE
	$check_key = substr($password, 0, 8);
	
	if ($check_key == $ADMIN_KEY) {
	   $user->roleID = 1; 
        } else {
           $user->roleID = 2;
        }

	// STORE PROFILE PICTURE
        if (!empty($avatar)) {
          $avatarName = $avatar->getClientOriginalName();
          $avatar->move('storage/avatar/', $avatarName);
          $user->avatar = $request->root().'/storage/avatar/'.$avatarName;
        }
	
	// PERSIST USER TO DATABASE
        if ($user->save()) 
            return Response::json(['success' => 'User created successfully!']);
	
        // LOG ERROR IF $user->save() RETURNS FALSE
        Log::error('Error: Account not created');  
        return Response::json(['error' => 'Account not created']);  
    }

    /**
     * User Log In.
     *
     * @param Illuminate\Support\Facades\Request::class

     * @return  Illuminate\Support\Facades\Response::class/
     */
    public function SignIn(Request $request) {

        // VALIDATION RULES
        $rules = [
            'password' => 'required',
            'email' => 'required'
        ];

        // VALIDATE INPUT
        $validator = Validator::make(Purifier::clean($request->all()), $rules);

        if ($validator->fails())
            return Responsee::json(['error' => 'You must enter email and password']) ;                           

	// VERIFY CREDENTIALS AND RETURN TOKEN IF SUCCESSFULL
        $credentials = $request->only('email', 'password');

        try {
            // attempt to verify the credentials and create a token for the user
            if (! $token = JWTAuth::attempt($credentials)) {
                return response()->json(['error' => 'invalid_credentials'], 401);
            }
        } catch (JWTException $e) {
            // something went wrong whilst attempting to encode the token
            return response()->json(['error' => 'could_not_create_token'], 500);
        }

        // all good so return the token
        return Response::json(compact('token'));
    }

    /**
     * Return data from app\User to populate user profile.
     *
     * @param void

     * @return  Auth::user
     */
    public function userProfile() {
        $user = Auth::user();
	
	if ($user->roleID != 2) { 
	   return Response::json(['error' => 'Not Authoried']); 	    
        } 
	
	$user = User::where('id', $user->id)->select( 'name', 'email', 'avatar')->first();     
        
    }

    /**
     * User event sign up
     *
     * @param Auth::user()->id 

     * @return int
     */
    public function join($id, $participants) {
        $user_id = Auth::id();      
        $event_id = (int) $id;
        $event_participants = (int) $participants;

        $check = Usertoevent::where('user_id', $user_id)->where('event_id', $event_id)->first();

        if (!empty($check))
            return Response::json(['error' => 'You already signed up for this event']);

        $user_to_event = new Usertoevent;
        $user_to_event->user_id = $user_id;
        $user_to_event->event_id = $event_id;

        if (!$user_to_event->save())
            return Response::json(['error ' => 'could not join event']);  
       
       $event = Event::where('id', $event_id)->first();
       $event->participants = $event_participants + 1;

       if ($event->save())
           return Response::json(['success' => 'joined event']);    
    } 


}
