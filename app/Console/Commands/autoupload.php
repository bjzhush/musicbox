<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Foundation\Inspiring;

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
                        $resJson = $this->uploadMusic($file, $config);
                        $resArr = json_decode($resJson, TRUE);
                        if (!is_null($resArr) && isset($resArr['code']) && $resArr['code'] == 200) {
                            $mv = rename($config['localDir'].'/'.$file, $config['uploadedDir'].'/'.$file);
                            if ($mv) {
                                $this->info('succeed upload '.$file);
                            } else {
                                $errorInfo = 'error moving file';
                                $this->error($errorInfo.$file);
                                $this->sendDesktopNotify($errorInfo);
                                return;
                            }
                        } else {
                            $this->error('response: '.$resJson);
                            $this->sendDesktopNotify($resJson);
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
    
    public function uploadMusic($file, $config)
    {
        if (!in_array($file, ['.','..'])) {
            $file = $config['localDir'].'/'.$file;

            if (function_exists('curl_file_create')) { // php 5.6+
                $cFile = curl_file_create($file);
            } else { //
                $cFile = realpath($file);
            }
            $post = array(
                'userid' => $config['userId'],
                'authkey' => $config['uploadAuthKey'],
                'comment' => 'phpauto',
                'mfile'=> $cFile
            );
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL,$config['uploadUrl']);
            curl_setopt($ch, CURLOPT_POST,1);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $post);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
            $result=curl_exec ($ch);
            curl_close ($ch);
            return $result;
        }

    }
    
    public function sendDesktopNotify($content) {
        file_put_contents('/tmp/notify_box.sh',"DISPLAY=:0.0 notify-send 'Allydata Exception' '".$content." ".date('Y-m-d H:i:s')."'".PHP_EOL, FILE_APPEND);
    }
}
