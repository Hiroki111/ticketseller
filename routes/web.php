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
    return "laravel";
});

Route::get('/concerts/{id}', 'ConcertsController@show')->name('concerts.show');
Route::post('/concerts/{id}/orders', 'ConcertOrdersController@store');
Route::get('/orders/{confirmationNumber}', 'OrdersController@show');

Route::get('/login', 'Auth\LoginController@showLoginForm');
Route::post('/login', 'Auth\LoginController@login');

Route::get('/backstage/concerts', function () {
    return "loggied in!";
});

Auth::routes();
Route::get('/home', 'HomeController@index')->name('home');
