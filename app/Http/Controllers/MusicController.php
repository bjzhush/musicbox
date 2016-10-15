<?php

namespace App\Http\Controllers;
use Illuminate\Http\Request;
use Qiniu\Auth as QiniuAuth;
use Auth;
use DB;


class MusicController extends Controller
{
    public function jsonSuccess($msg) {
        return json_encode(
            [
                'code' => 200,
                'msg' => $msg,
            ]
        );
    }

    public function jsonFail($msg) {
        return json_encode(
            [
                'code' => 500,
                'msg' => $msg,
            ]
        );
    }


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
        $artistName = $request->get('artist_name', NULl);
        $artistId = $request->get('artist_id', NULL);
        if (is_null($artistName) && is_null($artistId)) {
            return $this->jsonFail('no id and name found');
        }
        $musicId = $request->get('music_id', NULL);
        if (is_null($musicId)) {
            return $this->jsonFail('no musicid found');
        }
        if ($artistId == 0) {

            $repeatCheck = DB::table('artist')->select('id')->where('artist', $artistName)->first();
            if (!empty($repeatCheck)) {
                $artistId = $repeatCheck->id;
            } else {
                $artistId = DB::table('artist')->insert(
                    [
                        'artist' => $artistName,
                        'created_at' => date('Y-m-d H:i:s'),
                    ]
                );
            }
        }
        
        DB::table('music')
            ->where('id', $musicId)
            ->update(['artistid' => $artistId]);
        
        return $this->jsonSuccess('success');
    }
    
    public function viewEditMusic(Request $request)
    {
        $muiscId = $request->get('musicid');
        if (is_null($muiscId)) {
           exit('no music found');
        }
        $musicInfo = DB::table('music')->where('id', $muiscId)->first();

        if ($musicInfo->artistid > 0) {
            $artistRow = DB::table('artist')->select('artist')->where('id', $musicInfo->artistid)->first();
            $musicInfo->artist = $artistRow->artist;
        }

        return view('music.editmusic', [
            'musicInfo' => $musicInfo
        ]);
    }

    public function searchArtist(Request $request) {
       $artist = $request->get('key');
       if (strlen($artist) == 0) {
           $res = ['0' => '无结果'];
       } else {
           $sqlRes = DB::table('artist')
               ->where('artist', 'like', '%'.$artist.'%')
               ->take(10)
               ->get();
           if (empty($sqlRes)) {
               $res = ['0' => '无结果'];
           } else {
               $res = ['0' => '请选择'];
               foreach ($sqlRes as $row) {
                   $res[$row->id] = $row->artist;
               }
           }
       }
       return json_encode($res);
    }
}
