<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\User;
use App\Event;
use App\Usertoevent;
use Response;
use Purifier;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Log;
use Auth;
use JWTAuth;
use DateTime;

class EventController extends Controller {

  public function __construct() {
      $this->middleware('jwt.auth', ['only'=> [
          'store',
          'destroy',
      ]]);
  } 

  /**
   * CONVERT DATE FORMAT TO IS0-8601
   **/
    public function store(Request $request) {
	
	$user = Auth::user();

        // Constants
	$VALID_DATE_LENGTH = 10;
	$FIRST_PARTICIPANT = 1;
        $USER_ID = Auth::id();

        // GET FORM INPUT
        $name = $request->input('name');
        $category = $request->input('category');
        $date = $request->input('date');
	$owner_id = $user->id;	

	// CHECK FOR FILE UPLOAD ERROR
        if ($_FILES['image']['error'] !== UPLOAD_ERR_OK) 
            die("Upload failed with error code " . $_FILES['image']['error']);
       
      	
	// PROVIDE CHECKS FOR VALID IMAGE UPLOAD
        $info = getimagesize($_FILES['image']['tmp_name']);
	
        if ($info === FALSE) 
            die("Unable to determine image type of uploaded file");
        
        if (($info[2] !== IMAGETYPE_GIF) && ($info[2] !== IMAGETYPE_JPEG) && ($info[2] !== IMAGETYPE_PNG)) 
            die("Not a gif/jpeg/png");

	// GET PROFILE IMAGE INPUT
        $image = $request->file('image');


        // INPUT VALIDATION RULES
        $rules = [
            'name' => 'required',
            'category' => 'required',
            'date' => 'required',
            'image' => 'required'
        ];

        // VALIDATE AND ESCAPE INPUT
        $validator = Validator::make(Purifier::clean($request->all()), $rules);

        if ($validator->fails()) 
            return Response::json(['error' => 'You must fill out all fields.']);

        // ENSURE EVENT IS UNIQUE
        $check = Event::where('name', $name)->where('category', $category)->first();

        if (!empty($check))
            return Response::json(["error" => "Event name: $name aldready in exists in category: $category"]);
  

        // ENSURE VALID DATE INPUT
        if (strlen($date) != $VALID_DATE_LENGTH) {
            return Response::json(['error' => 'Date Expected in form mm/dd/yyyy']);
        }

        // separate string 'mm/dd/yyyy' into array by the '/' delimiter
        $date_array = explode( '/', $date);

        $month = $date_array[0];
        $day = $date_array[1];
        $year = $date_array[2];

        // ENSURE DATE EXISTS
        if (!checkdate($month, $day, $year))
            return Response::json(['error' => 'Invalid Date']);

        // CONVERT DATE FORMAT TO IS0-8601
        $mysqlDateFormat = $year.$month.$day;

        // SEND FORM INPUT TO DATABASE
        $event = new Event;
        $event->name = $name;
        $event->category = $category;
        $event->date = $mysqlDateFormat;
	$event->owner_id = $owner_id;
	$event->participants = $FIRST_PARTICIPANT;

	// STORE EVENT IMAGE
        if (!empty($image)) {
          $imageName = $image->getClientOriginalName();
          $image->move('storage/event/', $imageName);
          $event->image = $request->root().'/storage/event/'.$imageName;
        }
	
        $event->save();

        // UPDATE Usertoevents TABLE
        $stored_event = Event::where('name', $name)->where('category', $category)->first();

        if (empty($stored_event))
            return Response::json(["error" => "Event name: $name not persisted in category: $category"]);

        $event_id = $stored_event->id;
        
        $user_to_event = new Usertoevent;
        $user_to_event->user_id = $USER_ID;
        $user_to_event->event_id = $event_id;

        if ($user_to_event->save())
            return Response::json(['success' => 'event created successfully']);
  
               /**
         * ERROR LOGGING
         */
        Log::error('Error: Event not created');  
        return Response::json(['error' => 'Event not created']);  
    }    
	
    public function show() {
        $events = Event::all();
	return $events;
    }

   public function signUp() {

        if (!Auth::viaRemember())
            return Response::json(['error' => 'Must be logged in']);

        return Response::json(['success' => 'Sign up successfull']);
    }

   public function destroy() {
       //TODO
   }

}
