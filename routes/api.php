<?php

use Illuminate\Http\Request;

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

Route::middleware('auth:api')->get('/user', function (Request $request) {
    return $request->user();
});

Route::post('login', 'API\UserController@login');
Route::post('register', 'API\UserController@register');
Route::group(['middleware' => 'auth:api'], function(){
    Route::get('logout', 'API\UserController@logout');
    Route::get('details', 'API\UserController@details');
    Route::get('slider', 'API\UserController@slider');
    Route::get('getappointment', 'API\UserController@getappointment');
    Route::post('book', 'API\UserController@book');
});
