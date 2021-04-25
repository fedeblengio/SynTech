<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\User;

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

Route::middleware('auth:api')->get('/user', function (Request $request) {
    return $request->user();
});

Route::get('/test', function (){
    return 'Hola';
});

Route::get('/login','App\Http\Controllers\loginController@index');

Route::get('/prueba','App\Http\Controllers\usuariosController@index');
Route::get('/prueba3','App\Http\Controllers\usuariosController@store');

