<?php
namespace App\Libs;

class MusicService {
    
    public function qiniuUpload($uploadToken, $file, $originName, $uploadApi)
    {
        if (function_exists('curl_file_create')) { // php 5.6+
            $cFile = curl_file_create($file);
        } else { //
            $cFile = realpath($file);
        }
        $post = array('token' => $uploadToken, 'key' => date('YmdHis').'_'.$originName, 'file'=> $cFile);
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL,$uploadApi);
        curl_setopt($ch, CURLOPT_POST,1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $post);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
        $result=curl_exec ($ch);
        curl_close ($ch);
        return $result;
    }
    
}
