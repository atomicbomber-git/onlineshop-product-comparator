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

Route::redirect('/', '/recommendation/search');

Route::group(['prefix' => '/recommendation', 'as' => 'recommendation.'], function() {
    Route::get('/home', 'RecommendationController@home')->name('home');
    Route::get('/search', 'RecommendationController@search')->name('search');

    Route::get('/search/all', 'RecommendationController@searchAll')->name('search.all');
    Route::get('/search/bukalapak', 'RecommendationController@searchBukalapak')->name('search.bukalapak');
    Route::get('/search/shopee', 'RecommendationController@searchShopee')->name('search.shopee');
});