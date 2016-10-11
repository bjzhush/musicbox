<?php

/*
|--------------------------------------------------------------------------
| Application Routes
|--------------------------------------------------------------------------
|
| Here is where you can register all of the routes for an application.
| It's a breeze. Simply tell Laravel the URIs it should respond to
| and give it the controller to call when that URI is requested.
|
*/

Route::group(['middleware' => 'auth'], function () {
//上传
    Route::get('/uploadmusic', 'MusicController@viewUploadMusic');
    Route::post('/uploadmusic', 'MusicController@uploadMusic');


//列表
    Route::get('/listmusic', 'MusicController@viewListMusic');
    Route::post('/listmusic', 'MusicController@listMusic');
    Route::get('/','MusicController@viewListMusic');
    
});


Route::auth();

