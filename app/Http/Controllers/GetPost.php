<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class GetPost extends Controller
{
    /**
     * Handle the incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function __invoke()
    {
        //
        var_dump('hello world');
    }
}
