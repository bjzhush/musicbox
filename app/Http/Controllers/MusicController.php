<?php

namespace App\Http\Controllers;
use Illuminate\Http\Request;
use Qiniu\Auth as QiniuAuth;
use Auth;
use DB;


class MusicController extends Controller
{
    
    //获取当前用户的id
    public function getCrtUserId()
    {
        return Auth::User()->id;
    }

    public function viewUploadMusic(Request $request)
    {
        $msg = $request->input('msg','');
        if ($msg == 'success') {
           $msg = '<font color="green">Upload Success !</font>';
        } else {
           $msg = '<font color="red">'.$msg.'</font>';
        }
        return view('music.uploadmusic', ['msg' => $msg]);
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
           return redirect('/uploadmusic?msg=no file uploaded');
        }
        
        $files = $request->file('mfile');
        foreach($files as $file) {
            $fileTmpPath = $file->path();
            $md5sum = md5_file($fileTmpPath);

            $originName = $file->getClientOriginalName();

            $auth = new QiniuAuth(config('music.qiniu_accesskey'), config('music.qiniu_secretkey'));
            $uploadToken = $auth->uploadToken(config('music.qiniu_bucket'));

            $qiniuResponse = $this->qiniuUpload($uploadToken, $file, $originName, config('music.qiniu_upload_api'));

            $uploadResult = \Qiniu\json_decode($qiniuResponse, TRUE);

            if (isset($uploadResult['error'])) {
                echo 'Error:<font color="red">'.$uploadResult['error'].'</font>';
                exit;
            }

            DB::table('music')->insert([
                'uploadname' => $originName,
                'filemd5' => $md5sum,
                'qiniu_id' => $uploadResult['hash'],
                'uploadcomment' => $comment,
                'created_at' => date('Y-m-d H:i:s'),
            ]);
        }

        return redirect('/uploadmusic?msg=success');
        
    }

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
    

    public function listMusic(Request $request)
    {
        $musics = DB::table('music')
            ->where('user_id', $this->getCrtUserId())
            ->paginate(10);

        return view('music.listmusic', [
            'musics' => $musics,
        ]);
    }
    
    public function listen(Request $request)
    {
        
    }
    
    public function editMusic(Request $request)
    {
        $post = $request->all();
        echo "<pre>";
        var_dump($post);
        exit;
        
    }
    
    public function viewEditMusic(Request $request)
    {
        $muiscId = $request->get('musicid');
        if (is_null($muiscId)) {
           exit('no music found');
        }
        $musicInfo = DB::table('music')->where('id', $muiscId)->first();
        echo "<pre>";
        var_dump($musicInfo);
        exit;
        return view('music.editmusic', [
        ]);
    }
}
