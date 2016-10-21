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
        try {

            $comment = $request->input('comment', '');

            if (!$request->hasFile('mfile')) {
                return redirect('/uploadmusic?msg=no file uploaded');
            }

            $files = $request->file('mfile');
            
            foreach($files as $file) {
                $fileTmpPath = $file->path();
                $md5sum = md5_file($fileTmpPath);
                $fileSize = filesize($fileTmpPath);

                $md5sumCheck = DB::table('music')->where('filemd5', $md5sum)->first();
                if (!empty($md5sumCheck)) {
                    echo 'Error:<font color="red">重复文件(id: '.$md5sumCheck->id.')已存在</font><br>';
                    echo '点<a href="/uploadmusic">这里</a>返回';
                    exit;
                }

                $originName = $file->getClientOriginalName();

                $auth = new QiniuAuth(config('music.qiniu_accesskey'), config('music.qiniu_secretkey'));
                $uploadToken = $auth->uploadToken(config('music.qiniu_bucket'));
                
                $musicService = new \App\Libs\MusicService();

                $qiniuResponse = $musicService->qiniuUpload($uploadToken, $file, $originName, config('music.qiniu_upload_api'));

                $uploadResult = \Qiniu\json_decode($qiniuResponse, TRUE);

                if (isset($uploadResult['error'])) {
                    echo 'Error:<font color="red">'.$uploadResult['error'].'</font>';
                    exit;
                }

                DB::table('music')->insert([
                    'uploadname' => $originName,
                    'user_id' => $this->getCrtUserId(),
                    'filemd5' => $md5sum,
                    'filesize' => $fileSize,
                    'qiniu_id' => $uploadResult['hash'],
                    'qiniu_filename' => $uploadResult['key'],
                    'uploadcomment' => $comment,
                    'created_at' => date('Y-m-d H:i:s'),
                ]);
            }
                return redirect('/uploadmusic?msg=success');

        } catch (\Exception $e) {
            \App\Libs\LogService::logWrite('uploadException', $e->getMessage());
            echo $e->getMessage();
        }

    }
    
    public function insertMusic(Request $request)
    {
        $authKey = $request->get('authkey');
        if ($authKey !== env('AUTO_UPLOAD_AUTH_KEY')) {
            return $this->jsonFail('authkey fail');
        }
        $data = [
            'uploadname' => $request->get('uploadname'),
            'user_id' => $request->get('user_id'),
            'filemd5' => $request->get('filemd5'),
            'qiniu_id' => $request->get('qiniu_id'),
            'qiniu_filename' => $request->get('qiniu_filename'),
            'uploadcomment' => $request->get('uploadcomment'),
            'filesize' => $request->get('filesize'),
            'created_at' => $request->get('created_at'),
        ];
        $res = DB::table('music')->insert($data);
        if ($res) {
           return $this->jsonSuccess('success');
        } else {
           return $this->jsonFail('fail');
        }
    }
    
    public function jsonResponse($code, $msg) {
        return json_encode([
            'code' => $code,
            'msg' => $msg,
        ]);
    }

    

    public function listMusic(Request $request)
    {
        $mStatus = $request->get('mstatus');
        $dbMusic = DB::table('music')
            ->where('user_id', $this->getCrtUserId());
        if (strlen($mStatus)) {
            $dbMusic->where('marked', $mStatus);
        }
        $musics = $dbMusic->paginate();

        foreach ($musics as &$row) {
            $row->markHtml = $this->getMarkHtml($row);
            $row->previewUrl = $this->getQiniuPreviewUrl(
               config('music.qiniu_accesskey'),
               config('music.qiniu_secretkey'),
               config('music.qiniu_preview_domain'),
               $row->qiniu_filename
            );
        }

        return view('music.listmusic', [
            'musics' => $musics,
        ]);
    }

    public function getMarkHtml($row)
    {
        if ($row->marked == 0) {
           return '<font color="red">待处理</font>';
        } elseif ($row->marked == 1) {
           return '<font color="green">已处理</font>';
        } elseif ($row->marked == 2) {
           return '<font color="red">暂存</font>';
        } else {
           return '<font color="red">未知状态</font>';
        }
        
    }

    private function getQiniuPreviewUrl($ak, $sk, $previewDomain, $qiniuFileName) {
        $auth = new QiniuAuth($ak, $sk);
        return $auth->privateDownloadUrl($previewDomain.'/'.$qiniuFileName);
    }
    
    public function listen(Request $request)
    {
        $maxIdRow = DB::table('music')->select('id')->orderby('id','desc')->first();
        if (empty($maxIdRow)) {
           exit('No music found');
        }
        $maxId = $maxIdRow->id;
        $count = 0;
        while(!isset($randomRow) || empty($randomRow) && $count < 10) {
            $count++;
            $randomId = rand(1, $maxId);
            $randomRow = DB::table('music')->where('id', $randomId)->first();
        }
        $randomRow->listenUrl = $this->getQiniuPreviewUrl(
            config('music.qiniu_accesskey'),
            config('music.qiniu_secretkey'),
            config('music.qiniu_preview_domain'),
            $randomRow->qiniu_filename
        );
        
        
        return view('music.listen', [
            'randomRow' => $randomRow
        ]);
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

        //1正式，2draft
        $isDraft = $request->get('is_draft', 1);
        
        DB::table('music')
            ->where('id', $musicId)
            ->update([
                'artistid' => $artistId,
                'updated_at' => date('Y-m-d H:i:s'),
                'marked' => $isDraft,
            ]);
        
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
