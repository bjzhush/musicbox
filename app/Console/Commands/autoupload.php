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
        $config = [
            'userId' => env('AUTO_UPLOAD_USER_ID'),
            'uploadUrl' => env('AUTO_UPLOAD_URL'),
            'uploadAuthKey' => env('AUTO_UPLOAD_AUTH_KEY'),
            'localDir' => env('UPLOAD_LOCAL_DIR'),
            'uploadedDir' => env('UPLOADED_DIR'),
        ];
        
        foreach ($config as $k =>  $item) {
           if (is_null($item))  {
              $this->error('necessary key '.$k.' not config, exit!');
              exit;
           }
        }
        
        try {
            $musicFiles = scandir($config['localDir']);
            if (count($musicFiles)) {
                foreach($musicFiles as $file) {
                    $uploadResponse = $this->uploadMusic($file, $config);
                    $this->info($uploadResponse);
                }
                
            } else {
               sleep(200);
               $this->info('no task,sleeping');
            }
            
        } catch (\Exception $e) {
            \App\Libs\LogService::logWrite('exceptionlog', $e->getMessage());
        }
        
        $this->info('running');
    }
    
    public function uploadMusic($file, $config)
    {
        if (!in_array($file, ['.','..'])) {
            echo "<pre>";
            var_dump($file);
        }
        
    }
}
