<?php

use Illuminate\Http\Request;
use App\User;
//use Response;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

//Route::middleware('cors')->get('userName', function() {
//    return Response::json('Mr. Hands');
//});

//Route::middleware('cors')->get('publish', function() {
//    Redis::publish('test-channel', json_encode(['doo' => 'goo']));
//});

//Route::middleware('cors')->post('SignUp', 'UserController@store');
//Route::get('redis', function () {
  //  print_r(app()->make('redis'));    
//});
//Route::get('foo', 'UserController@foo');
/*Route::get('foo', function () {
    // Retrieve a piece of data from the session...
    $value = session('id_address');
    return $value;
});*/
//Route::get('showStuff', 'UserController@showStuff');
//Route::middleware('cors')->get('foo', 'UserController@foo');
//Route::get('processTest', 'UserController@process_test');
//Route::get('processTestTwo', 'UserController@process_test_two');
//Route::get('showImage', 'UserController@showImage');
//Route::post('signup', 'UserController@store');
//Route::middleware('cors')->post('signup', 'UserController@foo');
// Event routes
//Route::get('test', 'UserController@test');

// User Routes
Route::post('SignUp', 'UserController@store');
Route::post('login', 'UserController@SignIn');
Route::get('test', 'UserController@test');

// Event Routes
Route::post('storeevent', 'EventController@store');
Route::post('eventsignup', 'EventController@signUp');

// Invalid request route
Route::any('{path?}', 'MainController@index')->where("path", ".+");
