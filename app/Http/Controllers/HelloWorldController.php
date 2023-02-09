<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class HelloWorldController extends Controller
{
    public function homepage() {
        return '<h1>Hello, World... with Laravel!</h1>';
    }
}
