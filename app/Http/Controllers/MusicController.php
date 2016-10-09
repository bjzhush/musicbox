<?php

namespace App\Http\Controllers;
use Illuminate\Http\Request;
use Qiniu\Auth;
use DB;


class MusicController extends Controller
{
    const QINIU_AccessKey = '22h0jsT4bXwJ5MG3TAWcchEEMThBqZJjiY3FnhlB';
    const QINIU_SecretKey = 'DNzaFhms7s8dNBRXxdrrHr9FctqUEVVnRFTIH97H';
    const QINIU_BUCKET = 'musicbox';
    const QINIU_UPLOAD_API = 'http://up-z1.qiniu.com';

    public function viewUploadMusic()
    {
        return view('music.uploadmusic');
    }
    
    public function response($status = true, $msg)
    {
        $code = $status ? 'success' : 'failed';
        return json_encode([
            'code' => $code,
            'msg' => $msg,
        ]);
    }
    
    public function uploadMusic(Request $request)
    {
        $comment = $request->input('comment', '');
        
        if (!$request->hasFile('mfile')) {
           return $this->response(false, 'no file upload');
        }
        
        $file = $request->file('mfile');
        
        $fileTmpPath = $file->path();
        $md5sum = md5_file($fileTmpPath);
        
        $originName = $file->getClientOriginalName();

        $auth = new Auth(self::QINIU_AccessKey, self::QINIU_SecretKey);
        $uploadToken = $auth->uploadToken(self::QINIU_BUCKET);



        $qiniuResponse = $this->qiniuUpload($uploadToken, $file, $originName, self::QINIU_UPLOAD_API);

        $uploadResult = \Qiniu\json_decode($qiniuResponse, TRUE);

        if (isset($uploadResult['error'])) {
           echo 'Error:<font color="red">'.$uploadResult['error'].'</font>';
           exit;
        }

        DB::table('music')->insert([
            'uploadname' => $originName,
            'filemd5' => $md5sum,
            'uploadcomment' => $comment,
            'created_at' => date('Y-m-d H:i:s'),
        ]);
        
        echo "Upload succeed";
        
        
    }

    public function qiniuUpload($uploadToken, $file, $originName, $uploadApi)
    {
        if (function_exists('curl_file_create')) { // php 5.6+
            $cFile = curl_file_create($file);
        } else { //
            $cFile = realpath($file);
        }
        $post = array('token' => $uploadToken, 'key' => $originName, 'file'=> $file);
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL,$uploadApi);
        curl_setopt($ch, CURLOPT_POST,1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $post);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
        $result=curl_exec ($ch);
        curl_close ($ch);
        return $result;
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
