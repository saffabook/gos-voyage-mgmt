<?php

use App\Http\Controllers;
use Illuminate\Support\Facades\Route;

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

Route::group([
    'prefix' => 'posts'
    ], function () {
    Route::post('add', Controllers\Posts\AddPost::class);
    Route::get('/', Controllers\Posts\GetPosts::class);
    Route::get('posts', Controllers\Posts\GetPosts::class);
    Route::get('post/{id}', Controllers\Posts\GetPost::class);
    Route::post('update/{id}', Controllers\Posts\UpdatePost::class);
    Route::post('delete/{id}', Controllers\Posts\DeletePost::class);
});

Route::group([
    'prefix' => 'comments'
    ], function () {
    Route::post('add', Controllers\Comments\AddPostComment::class);
    Route::get('/', Controllers\Comments\GetPostComments::class);
    Route::get('comments', Controllers\Comments\GetPostComments::class);
    Route::get('comment/{id}', Controllers\Comments\GetPostComment::class);
    Route::post('update/{id}', Controllers\Comments\UpdatePostComment::class);
    Route::post('delete/{id}', Controllers\Comments\DeletePostComment::class);
});

Route::group([
    'prefix' => 'vessels'
    ], function () {
    Route::post('create', Controllers\Vessels\CreateVessel::class);
    Route::get('/', Controllers\Vessels\ListVessels::class);
    Route::post('get/{id}', Controllers\Vessels\GetVessel::class);
    Route::post('update', Controllers\Vessels\UpdateVessel::class);
    Route::post('delete/{id}', Controllers\Vessels\DeleteVessel::class);
});
