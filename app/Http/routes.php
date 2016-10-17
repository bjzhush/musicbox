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
    Route::any('/listmusic', 'MusicController@listMusic');
    Route::get('/','MusicController@listMusic');
    
    //编辑
    Route::get('/editmusic', 'MusicController@viewEditMusic');
    Route::post('/editmusic', 'MusicController@editMusic');
    
    Route::post('/searchartist', 'MusicController@searchArtist');

    Route::any('/listen', 'MusicController@listen');
    
});


Route::auth();

