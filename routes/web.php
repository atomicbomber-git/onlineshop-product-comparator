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

Auth::routes();

Route::get('/home', 'HomeController@index')->name('home');

Route::redirect('/', '/recommendation/home');

Route::group(['prefix' => '/recommendation', 'as' => 'recommendation.'], function() {
    Route::get('/home', 'RecommendationController@home')->name('home');
    Route::get('/search', 'RecommendationController@search')->name('search');
});