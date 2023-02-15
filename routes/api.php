<?php

use App\Http\Controllers;
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

Route::post('posts', Controllers\GetPost::class);

Route::get('posts', Controllers\GetPosts::class);

Route::get('posts/{id}', Controllers\GetPost::class);

Route::delete('posts/post-delete/{id}', Controllers\DeletePost::class);
