<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Foundation\Inspiring;
use Qiniu\Auth as QiniuAuth;

class autoupload extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'autoupload';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'auto upload music';

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $startDay = date('Y-m-d');
        $this->info('start running '.date('Y-m-d H:i:s'));
        $config = [
            'userId' => env('AUTO_UPLOAD_USER_ID'),
            'uploadUrl' => env('AUTO_UPLOAD_URL'),
            'uploadAuthKey' => env('AUTO_UPLOAD_AUTH_KEY'),
            'localDir' => env('UPLOAD_LOCAL_DIR'),
            'uploadedDir' => env('UPLOADED_DIR'),
            'failedDir' => env('FAILED_DIR'),
        ];
        
        foreach ($config as $k =>  $item) {
           if (is_null($item))  {
              $this->error('necessary key '.$k.' not config, exit!');
              return;
           }
        }
        
        try {
            while(date('Y-m-d') == $startDay) {
                $musicFiles = scandir($config['localDir']);
                foreach ($musicFiles as $k => $musicFile) {
                   if (in_array($musicFile, ['.','..']))  {
                      unset($musicFiles[$k]);
                   }
                }
                
                if (count($musicFiles)) {
                    foreach($musicFiles as $file) {

                        $auth = new QiniuAuth(config('music.qiniu_accesskey'), config('music.qiniu_secretkey'));
                        $uploadToken = $auth->uploadToken(config('music.qiniu_bucket'));

                        $musicService = new \App\Libs\MusicService();
                        $filePath = $config['localDir'].'/'.$file;

                        $qiniuResponse = $musicService->qiniuUpload($uploadToken, $filePath, $file, config('music.qiniu_upload_api'));

                        $uploadResult = \Qiniu\json_decode($qiniuResponse, TRUE);

                        if (!isset($uploadResult['error'])) {
                            $md5Sum = md5_file($config['localDir']).'/'.$file;
                            $mv = rename($config['localDir'].'/'.$file, $config['uploadedDir'].'/'.$file);
                            if ($mv) {
                                $insertResponse = $this->insertDb($file, $config, $uploadResult, $md5Sum);
                                $insertJson = json_decode($insertResponse, TRUE);
                                if (!isset($insertJson['code']) || $insertJson['code'] !== 200) {
                                    //插入数据库错误
                                    $this->error($insertResponse.$file);
                                    $this->sendDesktopNotify($insertResponse);
                                    return;
                                    
                                }
                                $this->info('succeed upload '.$file);
                            } else {
                                //移动文件错误
                                $errorInfo = 'error moving file';
                                $this->error($errorInfo.$file);
                                $this->sendDesktopNotify($errorInfo);
                                return;
                            }
                        } else {
                            //上传七牛错误
                            $this->error('response: '.$qiniuResponse);
                            $this->sendDesktopNotify($qiniuResponse);
                            return;
                        }
                    }

                } else {
                    $this->info('no task,sleeping 600');
                    sleep(600);
                }
            }
            
        } catch (\Exception $e) {
            echo $e->getMessage();
            $this->sendDesktopNotify($e->getMessage());
            \App\Libs\LogService::logWrite('exceptionlog', $e->getMessage());
        }
        
        $this->info('finish running '.date('Y-m-d H:i:s'));
    }
    
    public function insertDb($file, $config, $uploadResult, $md5sum)
    {

        $post = array(

            'uploadname' => $file,
            'user_id' => $config['userId'],
            'filemd5' => $md5sum,
            'qiniu_id' => $uploadResult['hash'],
            'qiniu_filename' => $uploadResult['key'],
            'uploadcomment' => 'php auto upload',
            'created_at' => date('Y-m-d H:i:s'),
            
            'userid' => $config['userId'],
            'authkey' => $config['uploadAuthKey'],
        );
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL,$config['uploadUrl']);
        curl_setopt($ch, CURLOPT_POST,1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $post);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT ,0);
        curl_setopt($ch, CURLOPT_TIMEOUT, 600); //timeout in seconds
        $result=curl_exec ($ch);
        curl_close ($ch);
        return $result;

    }
    
    public function sendDesktopNotify($content) {
        file_put_contents('/tmp/notify_box.sh',"DISPLAY=:0.0 notify-send 'MusicUpload Exception' '".$content." ".date('Y-m-d H:i:s')."'".PHP_EOL, FILE_APPEND);
    }
}
