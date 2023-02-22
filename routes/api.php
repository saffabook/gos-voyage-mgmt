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

// Routes to Crud posts
Route::post('posts', Controllers\GetPost::class);
Route::get('posts', Controllers\GetPosts::class);
Route::get('posts/{id}', Controllers\GetPost::class);
Route::delete('posts/post-delete/{id}', Controllers\DeletePost::class);
Route::post('post-add', Controllers\AddPost::class);
Route::post('posts/post-update/{id}', Controllers\UpdatePost::class);

// Routes to Crud comments
Route::post('comment-add', Controllers\AddPostComment::class);
Route::post('comments/comment-delete/{id}', Controllers\DeletePostComment::class);
Route::get('comments', Controllers\GetPostComments::class);
Route::get('comments/{id}', Controllers\GetPostComment::class);
Route::post('comments/comment-update/{id}', Controllers\UpdatePostComment::class);
