<?php
namespace App\Libs;

class LogService {

    public static function logWrite( $logName, $content, $daily = false) {
        $content = date('Y-m-d H:i:s').' '.$content;
        if (empty($log)) {
            static $log = null;
        }
        $log = new \Monolog\Logger('');
        if ($daily) {
            $dayFlag = date('ymd').'_';
        } else {
            $dayFlag = '';
        }
        $log->pushHandler(new \Monolog\Handler\StreamHandler(storage_path().'/logs/'.$dayFlag.$logName.'.log'), \Monolog\Logger::INFO);
        $log->addInfo($content);

        return;
    }


}
