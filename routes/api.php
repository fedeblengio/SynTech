<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\User;
use App\Http\Controllers;
use Carbon\Carbon;
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


Route::post('/login','App\Http\Controllers\loginController@connect');


Route::get('/test', function (){

 return "Estas en /test";
});



Route::get('/usuarios','App\Http\Controllers\usuariosController@index');
Route::get('/usuario','App\Http\Controllers\usuariosController@show');

Route::post('/usuario','App\Http\Controllers\usuariosController@create')->middleware('verificar_token');
Route::delete('/usuario','App\Http\Controllers\usuariosController@destroy')->middleware('verificar_token');
Route::put('/usuario','App\Http\Controllers\usuariosController@update')->middleware('verificar_token');


Route::post('/usuariosintoken','App\Http\Controllers\usuariosController@create');