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

/*  public function __construct() {

      $this->middleware('jwt.auth', ['only'=> [
          'store',
          'destroy',
      ]]);
  } */


  /**
   * CONVERT DATE FORMAT TO IS0-8601
   **/
    public function store(Request $request) {

        // Constants
        $VALID_DATE_LENGTH = 10;

        // GET FORM INPUT
        $name = $request->input('name');
        $category = $request->input('category');
        $date = $request->input('date');

        // INPUT VALIDATION RULES
        $rules = [
            'name' => 'required',
            'category' => 'required',
            'date' => 'required',	
        ];

        // VALIDATE AND ESCAPE INPUT
        $validator = Validator::make(Purifier::clean($request->all()), $rules);

        if ($validator->fails()) 
            return Response::json(['error' => 'You must fill out all fields.']);

        // ENSURE EVENT IS UNIQUE
        $check = Event::where('name', $name)->where('category', $category)->first();

        if (!empty($check))
            return Responsee::json(["error" => "Event name: $name aldready in exists in category: $category"]);
  

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
  
        if ($event->save());
            return Response::json(['success' => 'Event created successfully!']);

        /**
         * ERROR LOGGING
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
