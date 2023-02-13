<?php

use Illuminate\Http\Request;
// use Illuminate\Support\Facades\Route;


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


// Declare a URL and link the file to the URL
// Route::post('/posts/{id}', 'GetPost');

// Route::post('posts', [GetPost::class]);

Route::post('user/{id}', 'ProfileController');
