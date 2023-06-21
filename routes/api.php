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
    Route::post('/', Controllers\Vessels\ListVessels::class);
    Route::post('get/{id}', Controllers\Vessels\GetVessel::class);
    Route::post('update', Controllers\Vessels\UpdateVessel::class);
    Route::post('delete/{id}', Controllers\Vessels\DeleteVessel::class);
});

Route::group([
    'prefix' => 'cabins'
    ], function () {
    Route::post('create', Controllers\Cabins\CreateVesselCabin::class);
    Route::post('/', Controllers\Cabins\ListVesselCabins::class);
    Route::post('get/{id}', Controllers\Cabins\GetVesselCabin::class);
    Route::post('update', Controllers\Cabins\UpdateVesselCabin::class);
    Route::post('delete/{id}', Controllers\Cabins\DeleteVesselCabin::class);
});

Route::group([
    'prefix' => 'crew-cabins'
    ], function () {
    Route::post('create', Controllers\Cabins\CrewCabins\CreateCrewCabin::class);
    Route::post('/', Controllers\Cabins\CrewCabins\ListCrewCabins::class);
    Route::post('get/{id}', Controllers\Cabins\CrewCabins\GetCrewCabin::class);
    Route::post('update', Controllers\Cabins\CrewCabins\UpdateCrewCabin::class);
    Route::post('delete/{id}', Controllers\Cabins\CrewCabins\DeleteCrewCabin::class);
});

Route::group([
    'prefix' => 'cabin-additionals'
    ], function () {
    Route::post('create',
        Controllers\Cabins\MetaData\CreateVesselCabinAdditionals::class);
    Route::post('/',
        Controllers\Cabins\MetaData\ListVesselCabinAdditionals::class);
    Route::post('get/{id}',
        Controllers\Cabins\MetaData\GetVesselCabinAdditionals::class);
    Route::post('update',
        Controllers\Cabins\MetaData\UpdateVesselCabinAdditionals::class);
    Route::post('delete/{id}',
        Controllers\Cabins\MetaData\DeleteVesselCabinAdditionals::class);
});

Route::group([
    'prefix' => 'voyages'
    ], function () {
    Route::post('create', Controllers\Voyages\CreateVesselVoyage::class);
    Route::post('/get/{id}', Controllers\Voyages\GetVesselVoyage::class);
});

Route::group([
    'prefix' => 'ports'
    ], function () {
    Route::post('create', Controllers\Voyages\CreateVoyagePort::class);
    Route::post('get/{id}', Controllers\Voyages\GetVoyagePort::class);
    Route::post('update/{id}', Controllers\Voyages\UpdateVoyagePort::class);
    Route::post('delete/{id}', Controllers\Voyages\DeleteVoyagePort::class);
});

Route::group([
    'prefix' => 'prices'
    ], function () {
    Route::post('create', Controllers\Prices\CreateVoyageCabinPrice::class);
    Route::post('/', Controllers\Prices\ListVoyageCabinPrices::class);
    Route::post('get/{id}', Controllers\Prices\GetVoyageCabinPrice::class);
    Route::post('update/{id}', Controllers\Prices\UpdateVoyageCabinPrice::class);
    Route::post('delete/{id}', Controllers\Prices\DeleteVoyageCabinPrice::class);
});
