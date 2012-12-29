<?php

namespace moxl;

class MoxlLogger {
    public static $logfilename = 'moxl.log';
    
    public static function log($message, $priority = '') 
    {
        /*if($priority != '')
            syslog($priority, $message);
        else
            syslog(LOG_ERR, $message);*/
        if(!($lfp = fopen(self::$logfilename, 'w'))) {
            echo 'Cannot open log file';
        } else {
            if(flock($lfp, LOCK_EX)) {
                ftruncate($lfp, 0);
                fwrite($lfp, date('H:i:s')."\n".$message."\n\n");
                fflush($lfp);
                flock($lfp, LOCK_UN);
            }
            fclose($lfp);
        }
    }
}
