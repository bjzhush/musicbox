<?php

namespace App\Http\Controllers;
use Request;


class MusicController extends Controller
{
    public function viewUploadMusic()
    {
        return view('music.uploadmusic');
    }
    
    public function uploadMusic(Request $request)
    {
        
    }
    
    public function viewSearchMusic()
    {
        return view('music.searchmusic');
    }
    
    public function searchMusic(Request $request)
    {
        
    }
    
    public function viewListMusic(Request $request)
    {
        return view('music.listmusic');
    }

    public function listMusic(Request $request)
    {

    }
}
