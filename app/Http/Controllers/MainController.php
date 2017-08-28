<?php

namespace Steve\Http\Controllers;

use Illuminate\Http\Request;
use File;

class MainController extends Controller 
{
  public function index() 
  {
    return File::get('index.html');
  }
}
