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

class EventController extends Controller {

    // Create event	
    public function store(Request $request) {
        $rules = [
            'name' => 'required',
            'category' => 'required',
        ];

        $name = $request->input('name');
        $category = $request->input('category');

        $validator = Validator::make(Purifier::clean($request->all()), $rules);

        if ($validator->failes()) 
            return Response::json(['error' => 'You must fill out all fields.']);

        $check = Event::where('name', $request->input('name'))->where('category', $request->input('category'))->first();

        if (!empty($check))
            return Responsee::json(["error" => "Event name: $name aldready in exists in category: $category"]);

        $event = new Event;
        $event->name = $name;
        $event->category = $category;

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

}
