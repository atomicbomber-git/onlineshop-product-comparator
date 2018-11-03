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

    Route::get('/search/all', 'RecommendationController@searchAll')->name('search.all');
    Route::get('/search/bukalapak', 'RecommendationController@searchBukalapak')->name('search.bukalapak');
    Route::get('/search/elevenia', 'RecommendationController@searchElevenia')->name('search.elevenia');
    Route::get('/search/shopee', 'RecommendationController@searchShopee')->name('search.shopee');
    Route::get('/search/jdid', 'RecommendationController@searchJdid')->name('search.jdid');
});