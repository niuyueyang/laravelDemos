<?php

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::get('/', function () {
    return view('welcome');
});
Route::any('/add',['uses'=>'HomeController@add']);
Route::any('/update',['uses'=>'HomeController@update']);
Route::any('/show',['uses'=>'HomeController@show']);
Route::any('/del',['uses'=>'HomeController@del']);
Route::any('/page',['uses'=>'HomeController@page']);
Route::any('/captcha/{tmp}',['uses'=>'HomeController@captcha']);
Route::any('/verifyCaptcha',['uses'=>'HomeController@verifyCaptcha']);