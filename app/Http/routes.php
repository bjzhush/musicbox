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

Route::get('/', function () {
    return view('welcome');
});

//上传
Route::get('/uploadmusic', 'MusicController@viewUploadMusic');
Route::post('/uploadmusic', 'MusicController@uploadMusic');

//搜索
Route::get('/searchmusic', 'MusicController@viewSearchMusic');
Route::post('/searchmusic', 'MusicController@searchMusic');

//列表
Route::get('/listmusic', 'MusicController@viewListMusic');
Route::post('/listmusic', 'MusicController@listMusic');
