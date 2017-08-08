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

class EventController extends Controller {

  public function __construct() {

      $this->middleware('jwt.auth', ['only'=> [
          'store',
	  'destroy',
      ]]);
  }


    // Create event	
    public function store(Request $request) {
	
	// INPUT VALIDATION RULES

        $rules = [
            'name' => 'required',
            'category' => 'required',
	    'date' => 'required',	
        ];
	
	$validDateLength = 8;

        // GET FORM INPUT

	$name = $request->input('name');
        $category = $request->input('category');
        $date = $request->input('date');

	// ENSURE VALID DATE INPUT
	
	if (strlen($date) != $validDateLength)
            return Response::json(['error' => 'Invalid Date']);

	if (!checkdate($date))
            return Response::json(['error' => 'Invalid Date']);

	// CONVERT DATE FORMAT TO IS0-8601
        
  	$addSlash = substr_replace($date, '/', 2, 0);		
        $dateWithSlash = substr_replace($addSlash, '/', 5, 0);		

        $date_array = explode( '/', $dateWithSlash);
  	$mysqlDateFormat = $date_array[2].$date_array[0].$date_array[1];

	// VALIDATE AND ESCAPE INPUT

        $validator = Validator::make(Purifier::clean($request->all()), $rules);

        if ($validator->fails()) 
            return Response::json(['error' => 'You must fill out all fields.']);

        $check = Event::where('name', $name)->where('category', $category)->first();

        if (!empty($check))
            return Responsee::json(["error" => "Event name: $name aldready in exists in category: $category"]);

        $event = new Event;
        $event->name = $name;
        $event->category = $category;
        $event->date = $mysqlDateFormat;
	
        if ($event->save());
            return Response::json(['success' => 'Event created successfully!']);

        /**
         * TODO: Add date function to Log
         */
        Log::error('Error: Event not created');  
        return Response::json(['error' => 'Event not created']);  

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
